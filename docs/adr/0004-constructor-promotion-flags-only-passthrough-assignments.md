# 0004. The constructor-promotion rule flags only pass-through assignments

## Status

Accepted

## Context

A value object, DTO or entity should promote its constructor properties rather than declare a field and
copy a same-named parameter into it in the body. The declare-then-assign form is boilerplate the
language removed; keeping it is dead weight and a place for the field and the parameter to disagree.

`ConstructorPromotionRule` enforces this. But a constructor body assignment is not always convertible to
promotion. `$this->code = strtoupper($code)` transforms its input; `$this->id = $id ?? Uuid::v4()`
supplies a default; these need a body and cannot become a bare promoted parameter. A rule that flagged
every `$this->x = …` would demand impossible rewrites and be silenced wholesale — worse than no rule.

## Decision

Flag a constructor statement only when it is a plain pass-through: `$this->x = $x`, where the assigned
value is exactly a non-promoted constructor parameter of the class, assigned unchanged to a property.
Any assignment whose right-hand side is a transformation, a default, a call, or anything other than the
bare parameter variable is left untouched.

The rule fires in `ValueObject/`, `DTO/` and `Entity/` namespaces, and honours the `// Deliberate:` /
ADR fence.

This narrowness is the point, not an unfinished implementation. The flagged subset is exactly the set of
assignments that are always mechanically convertible to promotion with no change in behaviour, so every
report is actionable and correct. Broadening the rule to "any body assignment" would trade that
certainty for false positives.

## Consequences

- A constructor that transforms or defaults its inputs is not flagged, even though it declares fields
  and assigns them. That is intended: those bodies are legitimate, and the rule makes no claim about
  them.
- The rule catches the common, pure boilerplate case and only that case. It is not a completeness
  guarantee that every promotable property has been promoted — only that no plain pass-through remains.
- Do not "strengthen" the check to match transformed assignments. The narrow definition is what keeps
  every finding a safe, no-op refactor; widening it reintroduces the false positives this decision
  exists to avoid.
