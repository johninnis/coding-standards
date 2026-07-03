# 0010. Primitive-at-boundary is detected by matching a parameter name to a value object

## Status

Accepted

## Context

The ecosystem parses a primitive to its value object at the input boundary and threads the domain type
through every layer — a `PublicKey`, not a `string`; an `EventId`, not a raw hex. A bare primitive
crossing an internal boundary means a value object that should exist doesn't, or a parse that belongs
at the edge has leaked inward. `PrimitiveAtBoundaryRule` catches that.

The hard part is that no type fact says a given `string` *should* be a `PublicKey`. A blanket "no
primitives in domain signatures" would flag every `int` and be silenced on sight. The only durable
signal is the name: a parameter or property called `$publicKey`, typed `string`, when a `PublicKey`
value object exists.

Name-matching is a heuristic, with two failure modes to manage. It **over-matches** on generic concept
names — a `Message` value object would fire on every `string $message`, which is a text payload far more
often than the envelope. And it **under-matches** on role names — a `string $author` that is really a
public key never matches `PublicKey`.

## Decision

Detect the concept by name, gated on the value object existing, mirroring the collection-over-array
rule (ADR-0008):

- A collector records the short name of every `ValueObject/` class.
- The rule flags a `string`/`int` parameter or property in a `Domain/` or `Application/` class whose
  name — normalised for case and underscores — matches a recorded value object, and whose enclosing
  class is not that same value object (a value object wrapping its own primitive is the sanctioned parse
  edge).

Precision is bought two ways, both curated like the domain-purity denylist: a **length floor** drops
concepts under five characters (`Id`, `Url`, `Name`), and a **common-word list** drops longer concepts
that double as ordinary field names (`Message`, `Content`, `Value`, `Status`…). A value object of those
names matches too broadly to flag with confidence.

## Consequences

- Existence-gating keeps it quiet: a primitive with no value object to become is never flagged, and the
  suggestion always names a type that exists. An `int $timestamp` fires only where a `Timestamp` value
  object is present.
- The under-match is real and accepted. A role-named primitive (`$author`, `$recipient`) that should be
  a value object slips through — and that is where primitive obsession most often hides — so a clean run
  is not proof the boundary is fully typed. This rule is a floor, not a guarantee; it sits a tier below
  the type-fact rules (`equalsSelf`, `collectionOverArray`) in confidence, because a name is not a type.
- The common-word list will need extending as new generic-named value objects appear. Adding a word is
  expected maintenance. Do not remove the list or the length floor to raise recall: that reinstates the
  `string $message` → `Message` class of false positive the curation exists to prevent.
