# 0014. Misplaced unlayered code is caught by the contracts it declares

## Status

Accepted

## Context

ADR-0013 settled that layer membership is a namespace segment, and that a file carrying no such
segment is not layered code — the rule says nothing about it. That exemption is deliberate and load
bearing: it is what keeps `CleanArchitectureLayeringRule` quiet on a package's composition root,
whose whole job is to reach every layer and wire them together.

It is also a loophole, and one that is reached for by accident rather than by design. An Application
service that needs a Presentation collaborator fails the inward rule; moving the file to the package
root makes the failure disappear, because an unlayered file is no longer judged. Nothing about the
class changed — the outward dependency is still there — but both the rule and the reviewer's eye stop
seeing it. The move reads as a placement decision when it is really an unresolved dependency.

Catching that means answering a question the namespace alone cannot: is this unlayered file
composition, or is it layered code hiding? Requiring every `src/` class to carry a layer segment
answers it bluntly and wrongly — a survey of the ecosystem's 42 unlayered classes found 39 of them to
be this package's own PHPStan rules, which are not layered code and never will be, so the blunt form
needs a standing opt-out to be usable at all.

## Decision

`LayerPlacementRule` flags a class with no layer segment that **declares** a contract which has one:
`implements` on a class or enum, `extends` on a class or interface. Being bound to a Domain,
Application, Infrastructure or Presentation contract is the tell that a class is layered code, and it
must be filed under a layer so the inward rule applies to it.

- The remaining three unlayered classes in the ecosystem — `HostContainer`, `HostContainer`,
  `RelayContainer` — implement nothing. Composition constructs; it does not take on contracts. They
  stay silent, and so does every rule in this package, because `PHPStan\Rules\Rule` carries no layer
  segment and ADR-0013's `null`-layer skip already ignores it. The check needs no allowlist.
- Only declared contracts are read, not inherited or transitive ones. This matches the import-only
  reading of the rule it backs up: both report what the file says, so what a departure costs is
  visible in the file being judged.
- The fix a report asks for is not always to move the file. Where the class sits at the root because
  it would otherwise import outward, the dependency wants inverting behind a port — the file then
  belongs in a layer and lands there honestly. The message says to file it under a layer; it does not
  say which, because that is the design decision the report exists to force.

## Consequences

- The composition-root exemption survives intact, and is now narrow enough to state: unlayered means
  wiring only.
- A genuine root-level class that must implement a layered contract carries a `// Deliberate:` or
  `ADR-NNNN` fence like any other sanctioned departure.
- The rule is declaration-scoped, so a class implementing an unlayered interface that itself extends a
  layered one slips past. Walking the hierarchy would catch it at the cost of reporting a violation
  whose cause is in another file; the declared form keeps the report and its fix in one place.
- Interfaces are judged by what they extend, so an unlayered interface extending a layered one is
  flagged for the same reason a class is.
