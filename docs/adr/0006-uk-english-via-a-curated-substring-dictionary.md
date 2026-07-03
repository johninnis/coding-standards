# 0006. UK English is enforced with a curated substring dictionary over identifiers

## Status

Accepted

## Context

Identifiers are written in UK English. `UkEnglishRule` enforces it, and the whole rule turns on one
question: how do you tell a US spelling from a UK one mechanically, without a dictionary of the entire
language?

The tempting general rules are all wrong. A blanket `-ize`→`-ise` fires on `size`, `capsize`, `seize`,
`prize`. A blanket `-er`→`-re` fires inside `parameter`, `diameter`, `perimeter`. A blanket
`-or`→`-our` fires inside `collaborator` and `elaborate` (which contain `labor`). Splitting an
identifier into words and matching whole words misses the prefixed forms (`deserialize`,
`remodeling`). And matching a US stem that happens to be a prefix of its own UK form flags the
already-correct word (`catalog` inside `catalogue`).

## Decision

Match a curated map of specific US spellings to their UK forms as case-insensitive substrings, against
**declared identifier names only** — types, methods, properties, parameters, constants, enum cases.
String literals are never scanned.

The map is a deliberately maintained artefact, curated to be false-positive-free by construction:

- Only unambiguous stems are included. Coincidental-substring homographs are excluded outright — no
  `-er`/`-re` stems (`meter`, `centre`), no `labor` (inside `collaborator`).
- No stem that is a prefix of its own UK form is included (so `catalog`/`catalogue` and
  `dialog`/`dialogue` are left out rather than misreported).
- The generic pieces that *are* safe — `ization`→`isation`, `yze`→`yse` — are included, because no
  correct word contains them.

The suggested correction preserves the case of the matched segment, so `EventSerializer` yields
`EventSerialiser` and `MAX_COLOR` yields `MAX_COLOUR`.

A method carrying `#[\Override]` is exempt: its name comes from an interface or parent it implements —
a built-in such as `JsonSerializable::jsonSerialize`, or another package's contract — and is not ours
to respell. Only the declaring site is. The attribute is a reliable marker because the ecosystem
mandates it on every method that implements or overrides an inherited one.

## Consequences

- Scanning identifiers and not string values is what makes the wire-format carve-out automatic: a
  header name or protocol token written as a string keeps its spec spelling untouched, while the
  symbol names we choose are held to UK English.
- The dictionary is incomplete on purpose and grows by hand. A US spelling not yet in the map passes
  silently; adding it is expected maintenance. Completing it with a general suffix rule is the mistake
  this record exists to prevent — that trades the zero-false-positive property for noise on
  `parameter` and `size`.
- A word is only ever flagged toward a spelling the map contains; the rule proposes no correction it
  cannot name.
