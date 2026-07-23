# 0015. Port placement takes no fence

## Status

Accepted. Supersedes the fence clause of ADR-0012.

## Context

ADR-0012 gave `portPlacement` a `// Deliberate:` / ADR fence on the port interface, reasoning that
"the construction-ownership signal is high but not absolute": a package might construct a genuinely
host-replaceable port in one spot, and the fence was the way to say so.

Two years of the convention in use say otherwise. Across the twelve repositories in the ecosystem
there is not one fenced port, not one `ignoreErrors` entry for the identifier, and no mention of it
in any analyser config — while 112 files carry a fence for some other rule. The exception the fence
was built for has never been reached for. The README went further and described `portPlacement` as
one of the rules "you most often silence per case", which was never true of any repository.

The reason it is never needed is in ADR-0012's own consequences: construction *site* is
load-bearing, and moving the `new` out into host wiring is the sanctioned way to declare a driven
port. That is not a workaround — it is the same act that makes the interface a port in the
architecture. A fence offers a second route to the same claim that changes no code, which means a
port that needs one is a port in name only.

## Decision

`portPlacement` honours no fence. `PortInterfaceCollector` collects every `Application/Port`
interface outside a test namespace, and the "constructed by the package" guard is the rule's only
exemption.

This files `portPlacement` with the pure-fact tier of ADR-0011 rather than the opinion tier. The tier
test is whether a departure is a deliberate design or a mistake, and this one is a mistake: an
`Application/Port` interface whose implementation the package builds itself is mis-filed, and the
remedy is to move the interface to `Application/Service` or move the construction to the host.

## Consequences

- The remedy for a report is always a code change, never a comment. That is a narrowing: a project
  that did rely on a fence would begin failing on upgrade. None does — the change was verified
  against all twelve consumers before it was made.
- The fence-honouring set is now exactly the opinion tier ADR-0011 describes, plus the structural
  rules where a departure is a recorded design decision. `portPlacement` sits with layering, naming
  and `strictTypes`: fence-blind, because a departure has nowhere legitimate to hide.
- ADR-0012 stands in full apart from its closing clause. Construction ownership still decides
  port-versus-collaborator, and host-script and test constructions still do not count as ownership.
