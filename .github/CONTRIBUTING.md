# Contributing

## RFCs / Pull Requests
To propose changes you will need to create a fork of this repository. In this forked repository you can apply, test, and commit your changes.
For more information on how to fork a repository please check the [GitHub Documentation](https://docs.github.com/en/get-started/quickstart/fork-a-repo).

## Branch naming

For branch naming use the gitflow branch name prefixes:

| Type    | Branch prefix |
|---------|---------------|
| Feature | feature/      |
| Bugfix  | bugfix/       |

The prefix should be followed by the github issue number, and a descriptive branch name.

For example, if there is an issue with number 10, which describes a bug where certain data is not set on quote creation the branch name would look like this:
```text
bugfix/10-add-missing-data-on-quote-creation
```

## Applying your changes

Always use the master branch as a base for your changes.

1. Checkout the master branch
```sh
git checkout master
```
2. Pull down any upstream changes
```sh
git pull
```
3. Create a new branch to work on
```sh
git checkout -b feature/1234-descriptive-branch-name
```

After committing your changes you can open a pull request from your fork against the `develop` branch of this repository
