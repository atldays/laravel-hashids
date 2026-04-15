# Contributing

Thanks for contributing to `atldays/laravel-hashids`.

This document explains the basic expectations for contributions, commit messages, formatting and testing.

## Before You Start

Please make sure your change fits the goals of the package:

- keep the API predictable
- prefer Laravel-friendly developer experience
- avoid adding magic when a simple explicit API is enough
- keep model, HTTP and core layers clearly separated

If you are adding a new feature, try to cover:

- implementation
- tests
- documentation when the public API changes

## Commit Messages

This project uses **Conventional Commits**.

Please format commit messages like this:

```text
type(scope): short description
```

Examples:

```text
feat(rules): add model-aware hash id validation
fix(routing): respect disabled external hash ids
docs(readme): document form request helpers
test(attributes): cover conflicting salt attributes
refactor(core): simplify hash id resolver naming
```

Common commit types:

- `feat`
- `fix`
- `docs`
- `refactor`
- `test`
- `chore`

## Running Tests And Formatting

The recommended way to work with the project is through Docker.

### Format

```bash
./bin/docker-php "vendor/bin/pint --test"
```

To auto-fix formatting issues:

```bash
./bin/docker-php "vendor/bin/pint"
```

### Test

```bash
./bin/docker-php "composer test"
```

## Local Development

If you already have a compatible local PHP environment, you can also run tools locally instead of Docker.

Examples:

```bash
vendor/bin/pint --test
vendor/bin/pint
composer test
```

Docker is still the preferred option because it matches the project environment more closely.

## What To Include In A Contribution

For most changes, please include:

- focused code changes
- tests for the new behavior or bug fix
- updated docs when the package API changes

If you change public behavior, examples in `README.md` should stay in sync.

## Pull Request Notes

Try to keep pull requests small and focused.

A good pull request usually includes:

- what changed
- why it changed
- how it was tested
- any migration or upgrade notes, if needed

## Quality Checklist

Before opening or updating a pull request, make sure:

- tests pass
- formatting passes
- commit messages follow Conventional Commits
- public API changes are documented
