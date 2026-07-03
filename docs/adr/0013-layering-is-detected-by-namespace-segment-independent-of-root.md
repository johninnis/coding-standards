# 0013. Layer membership is a namespace segment, independent of the root namespace

## Status

Accepted

## Context

`CleanArchitectureLayeringRule` enforces inward-only dependencies: Domain imports only Domain,
Application imports Application/Domain, Infrastructure imports those plus itself, Presentation imports
any. To do that it must answer one question for the file under analysis and for each `use` import:
*which layer is this symbol in?*

There is no type fact for a layer. The only durable signal is the namespace: the codebase files a
symbol under a `Domain`, `Application`, `Infrastructure` or `Presentation` segment, and that segment is
its layer. `Layer::of` reads the first such segment anywhere in a fully-qualified name.

The tempting extra guard is a root allowlist — count a symbol as "ours to reason about" only if it
begins with a known core namespace like `App\`. It reads like a safety measure and is in fact a
coupling: it would tie a language-agnostic architectural rule to specific vendor prefixes, silence the
rule in any package with a different core namespace, and silence it on a first-party companion package
whose root differs. The rule takes no such list.

## Decision

Layer membership is the namespace segment and nothing else. The rule compares the file's layer with
each import's layer, and flags an inward violation, whenever **both** ends carry a recognised layer
segment — regardless of what root namespace either sits under.

- A file with no layer segment is not layered code; the rule says nothing about it.
- An import with no layer segment (`Psr\Log\LoggerInterface`, `Symfony\...`, an ordinary utility) has a
  `null` layer and is never flagged. This is what keeps the check quiet: the vast majority of
  third-party imports carry no layer segment and are invisible to the rule automatically, with no
  allowlist required.
- An import that *does* carry a layer segment is held to the inward rule even across package
  boundaries. A `Shop\Catalogue\Domain` file importing `Acme\Toolkit\Infrastructure\Store` is a real
  inward violation and is flagged; the root differing from the file's is irrelevant.

No configuration, no root allowlist. The layer vocabulary is the contract; adopting the segment names
is how a package — any package — opts in.

## Consequences

- The rule works in every package that files code under the four layer segments, which is the whole
  point. Do not add a first-party root list to "scope" the rule: it severs cross-package enforcement (a
  first-party library consumed under its own root) and couples a general rule to specific vendor names.
  The `null`-layer skip already scopes it correctly.
- A third-party dependency that itself files code under these exact segment names is subject to the
  rule: importing its `Infrastructure` class into your `Domain` will flag. That is a genuine inward
  violation and worth surfacing; the rare unwanted case carries a per-identifier `ignoreErrors`, the
  sanctioned project-wide silence.
- `Layer::of` keys on the *first* matching segment. A namespace that nests one layer word inside another
  path segment resolves to the outer one; the convention is that a layer segment is a standalone path
  element, and the naming rules enforce that elsewhere.
- The rule is deliberately import-only — it reads `use` statements, not fully-qualified inline
  references. A dependency written inline (`new \Other\Infrastructure\Thing()`) slips past; the common,
  readable form is the `use` import, and that is what the rule targets.
