# CAFeVDB Members App

A Nextcloud app giving the members of a project-based orchestra
read-only access to a selected subset of their own data stored in the
orchestra's [CAFeVDB](https://github.com/rotdrop/cafevdb) database.

## Background

CAFeVDB is an orchestra-management app for orchestras which work
project by project (there are no weekly rehearsals). Its use is
restricted to the management board of the orchestra.

This app is the counterpart for the club members. After logging in,
each member can see, but not change:

- their contact information,
- their project participation,
- their instrument insurances.

## Relation to CAFeVDB

The members app is a separate app with its own copy of the required
entities and types. CAFeVDB is **not** needed to install, build or test
this app, and the PHPUnit suite runs without it.

For actual use, however, CAFeVDB is required: it populates and
maintains the data shown by this app. At runtime the members app
obtains encryption keys, translations and project events (e.g. project
creation and deletion, used to maintain per-project groups and
folders) from CAFeVDB through small interfaces and adapters which are
resolved lazily. Without CAFeVDB installed on the same Nextcloud
instance the members app can be installed, but it is of no use.

## Development

```
make dev-setup-php
make phpunit
```

The tests run inside a Nextcloud server checkout, see
`.github/workflows/ci.yml` for the exact setup.

