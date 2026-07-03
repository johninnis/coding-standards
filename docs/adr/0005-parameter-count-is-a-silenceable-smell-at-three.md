# 0005. Parameter count is a silenceable smell at a threshold of three

## Status

Accepted

## Context

A unit that takes many parameters is usually doing too much: the long list is a symptom of a
responsibility that should be split, or of arguments that belong together in a value object.
`ParameterCountRule` surfaces that symptom.

Two things about it read like mistakes and need recording. First, the threshold — "more than three" —
is a round number with no formal basis, and any fixed line will look arbitrary at the boundary. Second,
PHPStan has no warning tier: every rule reports an *error*. So a design *smell* — a prompt to reconsider,
not a proven defect — has to be expressed as an error identifier, which on the surface contradicts its
advisory nature.

## Decision

Flag any function, method or constructor with four or more parameters. Treat the finding as a smell: it
reports through the normal error channel (there is no other), but its identifier,
`innis.tooManyParameters`, is one a project may legitimately silence per-identifier when a cohesive
value object genuinely carries the arguments.

Three is chosen as the largest count that still reads as a small, cohesive signature; four is where a
list starts to signal a hidden responsibility. The number is a deliberate convention, not a measurement,
and consistency across the codebase is the value it buys.

The message names the intended fix — decompose the unit — and explicitly warns against the wrong one:
bundling unrelated arguments into a parameter object to duck the count hides the design problem instead
of resolving it. The rule skips test namespaces, where fixture builders legitimately take many values.

One class of unit is exempt: the constructor of a `readonly` class whose parameters are all promoted
properties — a pure data record. Its fields are the cohesive value, and a wire-format value object (an
event with seven protocol fields, a metadata struct) has nowhere to decompose to; the count is the
shape of the data, not a hidden responsibility. Behavioural units keep the check — a method, a factory,
or a service constructor taking four collaborators is exactly what the signal is for. This exemption is
constructor-only and deliberate: a data-shaped static factory still reports, because it can choose to
take the constructor's cohesive inputs rather than a flat list.

## Consequences

- The finding is a reported error like any other and will fail an analysis run. A project that has
  weighed a specific signature and accepts it silences the identifier for that case, exactly as it would
  any other smell rule here. This is the sanctioned escape hatch, not a rule failure.
- The threshold is fixed, not configurable. One line, applied everywhere, is the convention; making it a
  per-project knob would dissolve the consistency the rule exists to enforce.
- A parameter object adopted only to lower the count, without a cohesive concept behind it, is not an
  improvement and should not be read as one — the smell has merely moved.
- Promoted constructor parameters count like any other; a wide promoted constructor is precisely the
  data class that a value object or decomposition is meant to address.
