# 0001. Domain purity is detected with a curated built-in denylist

## Status

Accepted

## Context

Domain code is worth keeping pure: a unit whose output depends only on its inputs is deterministic,
testable without fakes, and free of hidden coupling to the clock, the filesystem, the network, or the
request. The `DomainPurityRule` exists to hold that line mechanically.

But PHP has no effect system, and purity is not decidable from the syntax tree in general — a call
through `$fn()`, a method on an injected collaborator, or a wrapper function can all reach a side
effect that no local analysis will see. A rule that tried to *prove* purity would either be unsound or
drown the Domain in false positives on every ordinary method call.

The obvious-looking alternatives each fail: banning all function calls is absurd; following calls
transitively across the package is expensive and still blind at the first interface boundary; trusting
a review to catch impurity is the discipline this package is meant to replace.

## Decision

Detect the concrete, high-signal ways Domain code reaches for impurity, and flag exactly those:

- A call to a built-in from a curated denylist, grouped by effect — reading the clock, randomness, file
  and stream I/O, environment reads, output, sleeping, spawning a process, opening a socket.
- Constructing `DateTime` or `DateTimeImmutable` with no argument (which reads the system clock).
- Reading a request, session or global superglobal (`$_GET`, `$_SESSION`, `$GLOBALS`, and the rest).

The rule fires only inside a `Domain/` namespace, and honours the `// Deliberate:` / ADR fence.

The denylist is a deliberate, maintained artefact — not a stand-in for a general analysis that was too
hard to write. It targets the direct, in-Domain uses of standard-library effects, which are both the
common real violation and the ones a name-based check can identify with near-zero false positives.

## Consequences

- The check is intentionally incomplete. Impurity reached indirectly — through a passed callable, a
  collaborator method, or a first-party wrapper — is not caught here; that is the job of the layering
  rule and of pushing effects behind injected ports, not of this list.
- The denylist will need extending as new standard-library effects become relevant. Adding a name is
  expected maintenance, not evidence the approach is wrong. Do not "complete" it by switching to a
  general effect analysis PHP cannot support.
- The rule is scoped to the Domain layer on purpose. Application code legitimately orchestrates ports
  and is not held to the same in-body ban; widening the rule to other layers would misread that
  orchestration as impurity.
- Zero-argument `DateTime` construction is flagged, but `new DateTimeImmutable($trustedString)` is not:
  passing an explicit argument is deterministic, and the clock-reading case is the empty constructor.
