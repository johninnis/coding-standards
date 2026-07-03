# 0011. The deliberate-fence is node-scoped, and the opinion rules honour it

## Status

Accepted

## Context

A sanctioned departure from a rule is marked in the code with a `// Deliberate: …` comment or an
`ADR-NNNN` reference, pinned at the site that reads like a smell so the justification travels with the
code and a well-meaning refactor trips over it. Two properties make that mechanism actually work, and
each reads like it could safely be looser than it is.

First, a marker must silence only the unit it sits on, never the whole file. A one-type-per-file class
hides the difference — the file *is* the type — but a file with several independently-flagged units (a
factory with five wide-parameter methods, or more than one class) has to fence one without silencing
the rest: a marker on method A must not exempt method B. A file-wide match would read the same in the
common case and quietly over-exempt in the uncommon one.

Second, the mechanism has to reach the **smell/opinion rules** (parameter count, the catch-all
suffixes, primitive-at-boundary), because those are the ones *most* likely to carry a legitimate,
per-instance, recorded exception — a wide constructor that is an irreducible protocol shape, a genuine
lifecycle `Manager`, a boundary that deliberately takes a primitive. The alternative for such a case, a
project-wide `ignoreErrors` entry in the analyser config, buries the rationale away from the code,
matches on a fragile message/path, and silences the rule far more widely than the one exception needs.

## Decision

The fence is read from a node's own attached comments (the parser attaches a unit's leading line- and
doc-comments to that unit), so a rule checks the marker on the exact node it reports on — a method, a
class, a property. A rule may pass more than one node, treating the fence as present if any carries it,
so a member-level rule can also honour a fence on the enclosing class.

The opinion rules honour that fence, pinned at the smell site: parameter count on the method or
function, a catch-all suffix on the class, primitive-at-boundary on the method/property (or the
enclosing class). The structural and type-contract rules honour it at the same node scope, so a marker
on one unit never exempts an unrelated unit elsewhere in the file.

## Consequences

- A fence silences exactly the unit it sits on. `tooManyParameters` on one factory method is fenced
  without blinding the rule to the other four — the pattern works for the rule that needs it most.
- The code-pinned marker is the primary way to record a one-off exception; `ignoreErrors` is reserved
  for silencing a rule project-wide. The justification lives at the code, next to an ADR reference, not
  in the analyser config.
- The pure-fact rules (layering, naming, strict types, `overrideAttribute`, `typedConstants`, …) remain
  fence-blind on purpose: a departure there is a mistake, not a deliberate design, and has nowhere
  legitimate to hide.
- `ukEnglish` honours the fence through a hierarchical walk rather than the flat node scan the other
  opinion rules use, because its findings sit on parameters and individual constant items whose markers
  attach to an enclosing node — a parameter's to its method, a constant's to the `const` statement, a
  member's to its class. Parameters of a closure nested in a method body are the one thing it does not
  reach; declared API identifiers (types, members, parameters, constants, enum cases) are what it
  checks, and a closure local is not one.
- The fence reads a node's attached comments, so it does no file I/O or source caching of its own.
