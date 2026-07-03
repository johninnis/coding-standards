# 0009. A transformation is detected by a curated name prefix, and must not return void

## Status

Accepted

## Context

An immutable value, entity, or collection transforms by returning a new instance. A transformation that
returns `void` is the tell of the opposite — it mutates in place — or it invites a caller to write
`$vo->withStatus(…)` and silently drop the result. `TransformationReturnsSelfRule` catches that, and it
turns on two judgements that read like arbitrary lines: which methods count as transformations, and
which return types count as "not a new instance".

There is no marker that says "this method is a transformation", so the only durable signal is the naming
convention — `with*`, `add*`, `remove*`, `map`, `filter`, and the like. A prefix match is a heuristic,
and a naive one misfires: matching `add` as a bare prefix would fire on `address()`.

## Decision

Treat a public method as a transformation when its name matches a curated prefix from a fixed list
(`with`, `without`, `add`, `remove`, `append`, `prepend`, `push`, `map`, `filter`, `merge`, `plus`,
`minus`, `concat`, `deduplicate`, `sorted`, `reversed`) **at a camelCase boundary** — the prefix is the
whole name, or the character after it is uppercase. So `withStatus` and `add` match; `address` and
`mapper` do not.

Flag such a method when its declared return type is `void`, `never`, or `bool` — the three that cannot
be a new instance. A method with no declared return type is left alone. The rule applies in
`ValueObject/` and `Entity/` namespaces and to typed collections, and honours the ADR fence.

## Consequences

- The camelCase-boundary check is what makes the prefix list safe; without it `add`, `with`, and `map`
  would fire on unrelated words. Keep it if the list grows.
- Detection is incomplete on purpose. A transformation named outside the list (a domain-specific verb)
  is not checked, and a genuine command that happens to start with a listed prefix and returns `void`
  will be flagged — that is the case for the fence. Do not "improve" recall by dropping the
  camelCase-boundary guard or by matching every method returning `void`; both reintroduce the false
  positives this scoping removes.
- Only `void`/`never`/`bool` are treated as in-place tells. A transformation that returns some other
  wrong type (a scalar, an unrelated object) is not caught here; the high-signal, low-noise case is the
  `void` mutator, and that is what the rule targets.
