# Release Process

## Versioning

We follow [Semantic Versioning 2.0.0](https://semver.org/).

Given a version number MAJOR.MINOR.PATCH, increment the:

1. MAJOR version when you make incompatible API changes,
2. MINOR version when you add functionality in a backwards compatible manner, and
3. PATCH version when you make backwards compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.

## Release Steps

1. Ensure all tests pass and code quality checks are green on CI.
2. Update CHANGELOG.md for the upcoming release.
3. Update the version in composer.json.
4. Commit the changes.
5. Tag the commit with the version number (e.g., v1.0.0).
6. Push the tag to trigger the release on Packagist (if configured) or manually submit to Packagist.

## Automation

We use GitHub Actions for CI. To automate releases, we can use a workflow that:
- Runs on push to tags matching `v*`
- Builds the package
- Submits to Packagist

However, for the initial release, we will do it manually.

## Initial Release

We are releasing version 0.1.0 as the first unstable release.

- MAJOR=0: Initial development, anything may change.
- MINOR=1: First minor release with basic EntityManager and QueryBuilder.
- PATCH=0: First patch.

## Commands for Release

```bash
# Update version in composer.json to 0.1.0
# Update CHANGELOG.md for 0.1.0
git add composer.json CHANGELOG.md
git commit -m "Release 0.1.0"
git tag v0.1.0
git push origin main --tags
```

Then, manually submit to Packagist if not using an automated service.

## Packagist Submission

1. Go to https://packagist.org/packages/submit
2. Enter the repository URL: https://github.com/pbrilius/orm.git
3. Click "Check" and then "Submit"

After submission, Packagist will hook into the repository for updates.

## Next Versions

After the initial release, we can automate the release process with GitHub Actions.
