# 0002. Value parsers follow PHP's `from`/`tryFrom` split

## Status

Accepted

## Context

A named constructor that builds a value object from input has two honest shapes, and confusing them
is the defect this rule exists to prevent:

- **parsing untrusted input**, where bad input is an expected outcome the caller must handle. The
  correct shape returns the value or a null/`*Failure`, so "you didn't handle it" is a static-analysis
  error the caller cannot ignore. Throwing instead hides the failure from the analyser — a caller can
  forget the `catch` and the type system says nothing. This is the single most load-bearing
  error-handling decision in the ecosystem.
- **asserting an invariant on trusted input**, where bad input is a programming error. Throwing
  `InvalidArgumentException` is correct here, because no caller should recover.

The rule must tell the two apart, and no type or attribute marks which a method is. The durable signal
is the name — and PHP already draws exactly this line on backed enums: `Status::from()` throws on bad
input, `Status::tryFrom()` returns `null`. The ecosystem adopts that vocabulary rather than inventing
its own: a `tryFrom*` is the untrusted parser, a `from*` is total, trusted construction. Reusing the
language's own convention means every PHP developer reads the shape correctly with no rule to learn.

## Decision

Key on the `tryFrom*` and `from*` prefixes at a camelCase boundary — the prefix is the whole name, or
the character after it is uppercase — for a public static method that returns the type it constructs
(`self`, `static`, or the declaring class, possibly wrapped in a nullable or a union). A method whose
return type is not its own type is not a constructor of it and is left alone.

- A **`tryFrom*`** parses untrusted input: it must return nullable (`?self`, or a union carrying `null`
  or a `*Failure`) and must not `throw`.
- A **`from*`** is total, trusted construction: it must not return nullable. A nullable `from*` is a
  `tryFrom*` under the wrong name, and the finding says exactly that — rename it.

A `from*` is otherwise unconstrained: it may throw to assert an invariant, matching `BackedEnum::from`,
which is the sanctioned trusted-construction shape. All three checks are syntactic — the name, the
return type, and the presence of a `throw` in the body. The rule honours the `// Deliberate:` / ADR
fence for the rare `tryFrom*` that must translate a thrown library fault into a value at its own
boundary.

## Consequences

- The name carries the trusted/untrusted distinction, so the rule needs no heuristic to guess a
  method's intent and no per-class exemptions. A collection's lenient batch parser, a constructor whose
  first argument is an already-parsed domain object, and an invariant-asserting total constructor are
  all simply `from*` — total, free to throw, not required to be nullable — and none needs a special
  case.
- The one thing no name-based rule can catch is an untrusted parser deliberately written as a throwing
  `from*`. Under PHP's own semantics that is a legal choice (`from` throws), not a defect: the
  ecosystem's preference for outcomes-as-values is expressed by *choosing* the `tryFrom*` name, and the
  rule enforces the shape once that choice is made. A clean run is not proof every boundary parser
  reports failure as a value — it is proof that each is named for what it does.
- The throw check on `tryFrom*` is deliberately blunt: any `throw` anywhere in the body trips it,
  including inside a nested closure. A `tryFrom*` that must translate a thrown library error records a
  fence rather than the rule reasoning about where the throw sits.
- The suggested rename (`from*` → `tryFrom*`) is mechanical and always names a valid alternative, so
  every `fromIsTotal` finding is actionable.
- Extending the trigger to other prefixes (`parse*`, `of*`) is a future decision that would earn its
  own record; today the rule matches the two prefixes PHP itself established.
