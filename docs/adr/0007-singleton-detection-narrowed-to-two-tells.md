# 0007. Singleton detection is narrowed to two unambiguous tells

## Status

Accepted

## Context

A collaborator is reached through an injected interface, never a global access point. `NoSingletonRule`
enforces the ban on the singleton and service-locator shapes.

The obvious detection — "flag mutable static state" — is unusable. Static state has sanctioned uses in
this codebase: fixtures memoise expensive values in a static field, and caches are legitimate. Flagging
all of it would bury the real defect and be silenced wholesale.

A tighter-looking heuristic is also a trap: "private constructor plus a static method returning self"
describes the singleton, but it *equally* describes the ordinary value-object named constructor
(`private function __construct`, `public static function of(): self`). Keying on that shape would
misfire on nearly every value object in the codebase.

## Decision

Flag only the two shapes that mean "singleton" and nothing else:

- a `static` property whose declared type is the class itself (`self`, `static`, or its own name) — a
  class holding its own instance, and
- a `static getInstance()` accessor.

The rule skips test namespaces (where memoised fixtures live) and honours the `// Deliberate:` / ADR
fence.

The distinguishing feature of a singleton is that it caches and hands back *its own instance* through a
static access point. A static self-typed property is exactly that cache; a `getInstance` accessor is
exactly that access point. A named constructor returns a fresh instance and stores nothing static, so
it is untouched — which is the whole point of choosing these two signals over the private-constructor
heuristic.

## Consequences

- A value object with a private constructor and a `from*`/`of` named constructor is not flagged; it has
  no static self-instance and no `getInstance`. This is the false positive the narrow definition exists
  to avoid.
- A memoisation or lookup cache in a static field of some *other* type is not flagged; only a static
  field of the class's own type trips the rule.
- The rule is deliberately incomplete. A singleton that hides its instance behind an accessor named
  something other than `getInstance`, or in a differently-typed static field, slips through. Widening
  the detection to catch it would reintroduce the false positives on caches and named constructors, so
  the two-tell definition stays as is.
