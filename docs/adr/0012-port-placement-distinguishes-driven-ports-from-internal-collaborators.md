# 0012. Port placement is decided by who constructs the implementation

## Status

Accepted

## Context

Hexagonal architecture reserves `Application/Port` for one kind of interface: a *driven port* — a
boundary the host implements and injects, and the package itself never constructs. The package
declares the contract; the outside world supplies the implementation and can swap it.

An interface over an *internal collaborator* — a service the package builds itself with `new` — is a
different thing in the same clothes. It is a legitimate interface, but it is not a port: nothing
outside drives it, and filing it under `Port/` erodes the word until "port" means "any interface at
all". Such an interface belongs beside its implementation in `Application/Service`.

`PortPlacementRule` holds that line, and the difficulty is that the two are indistinguishable from a
single file: an `Application/Port` interface with an `Application/Service` implementor looks identical
whether the host wires it or the package news it up. The deciding fact — *does the package construct
the implementation itself?* — is not local; it is spread across every `new` site in the package.

## Decision

Decide port-versus-collaborator by construction ownership, computed across the whole package (a
`CollectedDataNode` rule fed by three collectors):

- **Port interfaces** — every non-fenced interface in an `\Application\Port` namespace, by short name.
- **Service implementors** — every `\Application\Service` class, with the short names of the interfaces
  it directly implements.
- **Self-constructed types** — the short name of every class `new`ed *inside a production-layer
  namespace* (Domain/Application/Infrastructure/Presentation).

Flag a port interface when it has an `Application/Service` implementor whose short name is in the
self-constructed set: the package builds its own implementation, so the interface is an internal
collaborator mis-filed under `Port/`.

What does **not** count as ownership is the crux. A `new` in the global namespace (a host wiring
script, an example or demo) or in a test namespace is the host or the harness supplying an
implementation — which is exactly what a driven port invites. Only construction by the package's own
layered code makes a collaborator internal.

Matching is by short name across collectors, as in ADR-0008 and ADR-0010, and the port interface
honours the `// Deliberate:` / ADR fence.

## Consequences

- The rule cannot run per file: ownership is a whole-package fact, which is why it is a collector-based
  rule rather than a node rule like the others.
- Construction *site*, not construction *existence*, is load-bearing. Moving the `new` out into a
  global-namespace host script is the sanctioned way to declare "this really is a driven port" — the
  same act that makes it true in the architecture makes the rule fall silent. Do not "tighten" the
  rule to count host-script or test constructions; that erases the very distinction it draws.
- Name matching carries ADR-0008's accepted cost: two classes sharing a short name across namespaces
  share a verdict, and an implementation reached only through a variable (`new $class()`) or a factory
  is invisible. The rule catches the direct, common shape and no more.
- Detection rides the `\Application\Port` and `\Application\Service` segment convention. An interface
  filed outside those segments is not seen; placement is enforced against the convention, and the
  convention is the source of truth.
- A port genuinely constructed by the package in one spot but truly host-replaceable carries a fence on
  the interface. The construction-ownership signal is high but not absolute, which is why the fence is
  honoured here rather than the rule being treated as a pure fact.
