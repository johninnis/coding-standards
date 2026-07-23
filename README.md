# innis/coding-standards

[![CI](https://github.com/johninnis/coding-standards/actions/workflows/ci.yml/badge.svg)](https://github.com/johninnis/coding-standards/actions/workflows/ci.yml)

> These rules encode the conventions of the **Innis ecosystem**. They are deliberately opinionated and
> almost certainly not to everyone's taste — that is by design. They are published for anyone extending
> the Innis libraries, or who simply wants to hold their own code to the same standards.

PHPStan rules that enforce the Innis coding conventions mechanically, inside the `phpstan analyse` run
every package already has. Because they run on the real parse tree and PHPStan's reflection, aliases,
grouped `use`, inline fully-qualified names, and non-standard formatting are all handled correctly.

## Installation

```sh
composer require --dev innis/coding-standards
```

Then include the extension in `phpstan.neon.dist` alongside the project's other includes:

```neon
includes:
    - vendor/innis/coding-standards/extension.neon
```

This manual `includes:` entry is required unless the project has
[`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) installed, in which
case the rules are registered automatically and no `includes:` line is needed.

Requires PHP 8.4+ and PHPStan `~2.2.2`. It runs at any PHPStan level; the rules are independent of
the level setting.

## What it enforces

Every rule reports with a stable `innis.*` identifier so it can be targeted in `ignoreErrors`. There
is no separate "warning" tier in PHPStan — everything below is a reported error — but the smell rules
(the `*Suffix` names and `tooManyParameters`) and `portPlacement` are the ones you most often silence
per case. Prefer a code-pinned `// Deliberate:` / ADR fence (see *Deliberate departures*) over a
project-wide `ignoreErrors` for a genuine one-off exception.

| Identifier | What it flags |
| --- | --- |
| `innis.strictTypes` | A file that declares a type but is missing `declare(strict_types=1)`. |
| `innis.noTraits` | Any `trait` (banned ecosystem-wide; share behaviour through an injected collaborator). |
| `innis.cleanArchitecture` | A `use` import that violates inward-only layering — Domain→Domain only; Application→Application/Domain; Infrastructure→Infrastructure/Application/Domain; Presentation→any. Catches cross-package leaks too. |
| `innis.layerPlacement` | A class filed under no layer segment that declares a contract carrying one (`implements` on a class or enum, `extends` on a class or interface). Unlayered code is exempt from the layering rule, and that exemption is for the composition root; being bound to a layered contract means the class is layered code and must be filed where the inward rule sees it. A class sitting outside the layers to avoid importing outward wants the dependency inverted, not the file moved. An anonymous class has no namespace of its own and is judged where it is declared, so an inline double in a test, one built inside a layered file, and one in an unnamespaced entry-point script are all silent. |
| `innis.interfaceNaming` | An `interface` whose name does not end in `Interface`. |
| `innis.collectionPlacement` | A `*Collection` class, or one extending `TypedCollection`, outside a `Collection/` namespace. |
| `innis.collectionFlat` | Sub-grouping under `Collection/` by concept (`Collection/Reference/…`). |
| `innis.valueObjectImmutable` | A concrete `ValueObject/` class that is not `final` or not a `readonly` class. Property-level `readonly` (the sanctioned memory-zeroing exception) reports with a tip so a human confirms the reason. `abstract` sum-type bases and fenced classes are exempt. |
| `innis.failureNotThrowable` | A `*Failure` (returned-outcome) type that is throwable — a fault uses the `*Exception` suffix. |
| `innis.exceptionPlacement` | A throwable / `*Exception` class outside an `Exception/` namespace. |
| `innis.exceptionShape` | An exception class that is neither `final` (leaf) nor `abstract` (base). |
| `innis.noDomainRepository` | Any type filed under a `Domain\Repository` namespace. A persistence store is a driven port the host supplies and can swap, so its interface belongs in `Application/Port/` beside the clock and the HTTP client — there is no `Domain/Repository`. |
| `innis.portPlacement` | An `Application/Port/` interface whose implementation is an `Application/Service/` class the package constructs itself — a mis-filed internal collaborator; its interface belongs in `Application/Service/`. |
| `innis.domainPurity` | A `Domain/` namespace that reaches for impurity directly: a built-in that reads the clock, uses randomness, performs I/O, reads the environment, writes output, pauses, spawns a process or opens a socket; a zero-argument `DateTime`/`DateTimeImmutable`; or a request/session/global superglobal. Push the effect behind a port. |
| `innis.tryFromReturnsNullable` / `innis.tryFromDoesNotThrow` / `innis.fromIsTotal` | Named constructors follow PHP's from/tryFrom split. A `tryFrom*` parses untrusted input: it returns `?self` (or a `*Failure`) and never throws, so a missed failure is an analyser error. A `from*` is total, trusted construction: it may throw to assert an invariant and does not return nullable — a nullable `from*` is a `tryFrom*` under the wrong name. |
| `innis.overrideAttribute` | A method that implements an interface method or overrides a parent method without `#[\Override]`. |
| `innis.promoteConstructorProperties` | A `ValueObject/`, `DTO/` or `Entity/` constructor that declares a field and assigns a same-named parameter to it in the body, instead of promoting the property. Only a plain pass-through assignment is flagged. |
| `innis.typedConstants` | A class, interface or enum constant declared without a type. |
| `innis.collectionContract` | A concrete typed collection (a `*Collection`, or a leaf extending `TypedCollection`) that is not `final` or does not implement `IteratorAggregate` + `Countable`. |
| `innis.errorSuffix` | A non-throwable class named `*Error` — a returned outcome value uses the `*Failure` suffix (`\Error` is a `Throwable`). |
| `innis.valueObjectAccessors` | A property hook on a `ValueObject/`, or asymmetric visibility (`private(set)`) on a `ValueObject/` or `Entity/` — keep a uniform `getX()` read surface. |
| `innis.noEmojis` | An emoji anywhere in a source file (code, comment, or string). |
| `innis.noSingleton` | A static property typed as its own class, or a static `getInstance()` accessor — a singleton/service locator; inject an interface instead. |
| `innis.ukEnglish` | A US spelling in a declared identifier (type/method/property/parameter/constant/enum-case name); string values are left alone. |
| `innis.collectionOverArray` | A public signature that passes an array of an element for which a typed collection exists — pass the `<Element>Collection`, not a generic array. |
| `innis.primitiveAtBoundary` | A `string`/`int` parameter or property in Domain/Application whose name matches an existing value object — parse the primitive to that value object at the boundary and thread it through. |
| `innis.equalsSelf` | An equality method (`equals`/`isEqualTo`/`sameValueAs`/…) on a value object or entity whose parameter is not typed `self` — a widened parameter lets a sibling type be compared. |
| `innis.transformationReturnsSelf` | A transformation (`with*`/`add*`/`remove*`/`map`/`filter`/…) on an immutable value, entity, or collection that returns `void`/`bool`/`never` instead of a new instance. |
| `innis.adapterSuffix` / `innis.managerSuffix` / `innis.serviceSuffix` | A catch-all `*Adapter` / `*Manager` / `*Service` class name — name the class for what it does. |
| `innis.tooManyParameters` | A function, method or constructor with more than three parameters — decompose the unit rather than bundling arguments into a parameter object. |

## Deliberate departures

The rules that can have a legitimate exception honour the ecosystem's Chesterton's-Fence convention: a
`// Deliberate: …` comment or an `ADR-NNNN` reference **pinned at the code** marks a justified departure
and is skipped. The marker is read from the reported node's own comments (see ADR-0011), so it is
scoped to exactly the unit it sits on — a fence on one method silences that method, not its siblings.
Rules that also look at the enclosing class let a class-level fence cover its members; `tooManyParameters`
is pinned to the method or function it flags, so a wide constructor is fenced on the constructor, not the
class.

- **Structural / type-contract rules** (`valueObjectImmutable`, `noDomainRepository`,
  `layerPlacement`, `portPlacement`, `domainPurity`, `tryFromReturnsNullable`/`tryFromDoesNotThrow`/`fromIsTotal`, `promoteConstructorProperties`,
  `noSingleton`, `equalsSelf`, `transformationReturnsSelf`) honour a fence on the class (or, for the
  per-method ones, on the method).
- **Smell / opinion rules** (`tooManyParameters`, the `*Suffix` names, `primitiveAtBoundary`,
  `ukEnglish`) honour a fence too — on the method for a parameter-count smell, the class for a suffix
  smell, the method/property/class for a primitive-at-boundary, and the declaration (or its enclosing
  class) whose name is US-spelled for `ukEnglish`. Pin the fence at the smell site, with an ADR.

Layering, trait, naming, `overrideAttribute`, `typedConstants`, `collectionContract`,
`valueObjectAccessors`, `noEmojis` and `collectionOverArray` are **always enforced** — a departure there
is an immediate refactor, not a comment. Several rules also relax in test code: `ukEnglish`/`noEmojis`
skip test files, `overrideAttribute` enforces only first-party contracts there (framework overrides like
`setUp` are exempt), `tooManyParameters` exempts data-record constructors, and `layerPlacement` skips
test namespaces — which covers an inline anonymous double, since it is judged where it is declared.

To silence a rule project-wide (rather than a single site), target its identifier:

```neon
parameters:
    ignoreErrors:
        - identifier: innis.serviceSuffix
```

## Development

```sh
composer test          # phpunit + phpstan (self-analysis, dogfooding these rules)
composer analyse       # phpstan only
composer check-style   # php-cs-fixer dry-run
```
