# 0008. Collection-over-array is keyed on the collection's name and gated on its existence

## Status

Accepted

## Context

A boundary that passes `list<Event>` instead of an `EventCollection` throws away the guarantee the
collection exists to provide: the element type, established once at construction, is instead re-asserted
(or silently trusted) at every call site. `CollectionOverArrayRule` closes that gap — but only where it
can do so without noise.

The rule needs two facts a single file cannot supply: which typed collections exist across the whole
package, and which element each one wraps. Resolving the element from the `@extends TypedCollection<E>`
generic is possible but fragile — the base type lives in the consuming package, not here, and the
generic binding is awkward to read back through reflection. And flagging *every* array of objects would
be unusable: plenty of arrays are legitimate, and no collection exists to replace them.

## Decision

Detect collections and their elements **by name**, and flag an array only when a matching collection
**exists**:

- A collector records, for every class named `<Element>Collection` (excluding the `TypedCollection`
  base), the element short name `<Element>` — the ecosystem's naming convention is the source of truth.
- A second collector records every public signature (parameter or return) whose resolved type is an
  array of an object, with that object's short name.
- The rule flags a usage only when its element short name is in the set of collected collection
  elements. A bare `array`, an array of a scalar, or an array of an object with no `<Element>Collection`
  is never flagged.

The typed collections themselves are exempt: their constructors take an array at the sanctioned
array-to-collection edge.

## Consequences

- Existence-gating is what keeps the rule quiet: it speaks only when a concrete collection is available
  to name in the fix, so every finding is actionable. An array of an element with no collection passes
  silently — by design, not oversight.
- Detection rides the `<Element>Collection` naming convention. A collection that breaks the convention
  is invisible to the rule, and two different `Event` types in separate namespaces share a short name
  and so share a verdict. This is the accepted cost of a name key over generic resolution; the naming
  convention is enforced elsewhere, so the coupling is safe in practice.
- Only whole-array types are matched. A `?list<Event>` or an `iterable<Event>` slips through; widening
  to those would trade the low false-positive rate for edge-case coverage, and is deliberately not done.
