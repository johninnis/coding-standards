# 0003. `#[\Override]` is required on interface implementations, not only parent overrides

## Status

Accepted

## Context

`#[\Override]` makes a method fail to compile if it no longer matches an inherited method of the same
name — the safety net that catches a signature drifting out from under a subtype when a base or an
interface changes. The codebase leans on an abstract-base / final-leaf shape and on narrow interfaces,
so this net is worth having everywhere it can apply.

PHP applies `#[\Override]` to two situations: a method overriding a parent class method, and a method
implementing an interface method. Many developers associate the attribute only with class inheritance
and would read "put it on interface implementations too" as noise — which is why the broader rule is
recorded here.

## Decision

`OverrideAttributeRule` requires `#[\Override]` on any method that, by reflection, implements an
interface method or overrides an ancestor method — the two cases PHP itself recognises, treated
identically.

Three kinds of method are exempt because they participate in no override contract: constructors
(not inherited as a contract), private methods (never override), and abstract methods (they declare a
contract rather than satisfy one).

Test code is held to a narrower bar rather than skipped wholesale. There the attribute is required only
for a **first-party** contract — a method whose declaring interface or parent lives in this codebase,
not under `vendor/` and not a PHP built-in — decided by the declaring class's file, so it stays
independent of the root namespace (as ADR-0013 does for layering). This split is deliberate: a
hand-written double of a first-party interface is exactly where the net earns its keep — rename a method
on the interface and the stale double fails loudly — so it is enforced. What is spared is the pervasive
framework override: every `setUp`/`tearDown` on a PHPUnit `TestCase`, and every built-in such as
`Stringable`, would otherwise demand the attribute across the whole suite for no drift-catching value,
since those APIs do not move. A test double declared as an anonymous class carries no `Tests` segment in
its own name, so the enclosing file's namespace decides whether a class is test code.

The rule has no fence: where it fires, a missing attribute is never a deliberate design, just a gap in
the safety net. In production every contract counts; the first-party narrowing applies to test code
alone.

## Consequences

- Every `Rule` and `Collector` in this package carries `#[\Override]` on `getNodeType()` and
  `processNode()`, since those implement framework interfaces. The package enforces this rule on its own
  source, so the requirement and the code that meets it stay in lockstep.
- Adding a method to an interface, or renaming one, now breaks every implementor that has drifted, at
  analysis time rather than at runtime.
- Detection is by reflection over parents and interfaces, so it is correct across grouped `use`,
  aliases, and inherited interfaces — not fooled by how the implements clause is written.
