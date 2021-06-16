# API Specific CI/CD Approach
This API is a different from the common-ci approach.

Build and Test stage takes `buildType` parameter that can have two values either `Development` or `Production`.

`Development:` Build and test stage will use submodule references for dependencies where the dependency is relative to the local file system, so git references need to be updated to refer to the updated dependencies.
`Production:` Build and test stage will use the publically released packages so in this case composer.json will need to be updated
to get the updated packages.

By default build-and-test.yml uses the submodules reference and tag-repository.yml uses the public packages.