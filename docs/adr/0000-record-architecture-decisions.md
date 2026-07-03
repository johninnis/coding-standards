# 0000. Record architecture decisions

## Status

Accepted

## Context

This package makes design choices that a competent reader could mistake for a mistake: a hard-coded
list of function names, a rule that fires on a naming prefix, a check that deliberately covers only a
subset of the cases it names. Left unexplained, each is a standing invitation for a later contributor
to "fix" it — to complete the list, generalise the heuristic, or delete the narrowing — and thereby
undo a decision that was made on purpose and paid for.

The "why" behind such a choice does not survive in the code, and it does not survive in the people who
made it. It needs a home that travels with the repository and outlives both.

## Decision

Record every architectural decision that is not self-evident as an Architecture Decision Record, using
Michael Nygard's format (2011).

- Records live in `docs/adr/`, one file per decision, named `NNNN-kebab-summary.md` with a
  zero-padded sequential number. This record, establishing the practice, is `0000`; substantive
  decisions start at `0001`.
- Every record has exactly four sections: **Status** (`Proposed` | `Accepted` | `Superseded by
  ADR-NNNN`), **Context** (the forces in play), **Decision** (the choice, stated as a rule), and
  **Consequences** (what it costs and forbids, including the specific thing a future reader will be
  tempted to change).
- Records are immutable. A decision is never edited to reverse it; a new record supersedes it, and the
  old one's Status is updated to point forward.
- Each record stands on its own engineering merits. It does not appeal to an external policy document
  as its justification — if the only reason for a choice is "a policy told me to", the real reason has
  not yet been found.

## Consequences

- A reviewer reads `docs/adr/` before judging the code, and does not flag or "simplify" anything a
  record already justifies.
- Writing a record is part of making a non-obvious decision, not a follow-up task to be deferred.
- The log grows append-only. History is preserved: a superseded decision remains readable alongside
  the one that replaced it.
