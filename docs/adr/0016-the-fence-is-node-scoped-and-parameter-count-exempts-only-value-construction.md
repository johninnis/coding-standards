# 16. The fence is node-scoped, and parameter count exempts only value construction

## Status

Accepted. Supersedes ADR-0011.

## Context

ADR-0011 made the deliberate-fence node-scoped and had the opinion rules honour it, parameter count among them. A sanctioned departure is marked with a `// Deliberate: …` comment or an `ADR-NNNN` reference at the site that reads like a smell, so the justification travels with the code. A marker must silence only the unit it sits on: a file with several independently flagged units has to fence one without silencing the rest. A project-wide `ignoreErrors` entry buries the rationale away from the code and silences far more than one exception needs.

Two parts of ADR-0011 no longer hold for parameter count. `tooManyParameters` exempted any `readonly` class whose constructor promoted every parameter, on the reasoning that such a class is a pure data record; a service whose constructor only promotes its injected collaborators reads exactly the same, so a service with four dependencies passed as if it were a value. And the fences it honoured excused either a wide value-object factory, which should not need one, or a service with too many collaborators, which a comment does not fix. A value's constructor and named constructors take the value's own fields, so the limit applies to behaviour, never to constructing a value, and a design rule is not exempted.

## Decision

- **The fence is read from a node's own attached comments**, so a rule checks the marker on the exact node it reports on: a method, a class, a property. A rule may pass more than one node, treating the fence as present if any carries it, so a member-level rule can honour a fence on the enclosing class.
- **The opinion rules honour that fence at the smell site:** a catch-all suffix on the class, primitive-at-boundary on the method or property (or the enclosing class). The structural and type-contract rules honour it at the same node scope, so a marker on one unit never exempts another unit in the file.
- **Parameter count honours no fence.** It sits in the fence-blind tier beside `portPlacement` (ADR-0015). Its only exemption is constructing a value: a method is exempt when its class is `final readonly`, its namespace has a `ValueObject`, `Entity`, `DTO` or `ViewModel` segment, and it is the constructor or a static method whose declared return type is `self` or `static`, nullable or not. Every other function, method and constructor with more than three parameters is reported.

## Consequences

- A fence silences exactly the unit it sits on.
- The code-pinned marker is the way to record a one-off exception for the rules that honour it; `ignoreErrors` is reserved for silencing a rule project-wide. The justification lives at the code, next to an ADR reference.
- The pure-fact rules (layering, naming, strict types, `overrideAttribute`, `typedConstants`, …) and parameter count are fence-blind: a departure there is a mistake, not a deliberate design.
- `ukEnglish` honours the fence through a hierarchical walk rather than a flat node scan, because its findings sit on parameters and constant items whose markers attach to an enclosing node. Parameters of a closure nested in a method body are the one thing it does not reach.
- The fence reads a node's attached comments, so it does no file I/O or source caching of its own.
- A service constructor is judged like any other method; `readonly` promotion no longer hides a fifth collaborator. An instance method or a static helper on a value that does not return the value is still counted.
- DTOs and view models are values too, so the same namespace-plus-`final readonly` test covers them. A wide value outside those namespaces, or not `final readonly`, is reported; moving it there or making it immutable is the remedy.
- A consumer that relied on a fence or on `readonly` promotion for this identifier begins failing on upgrade, and the remedy is a code change.
