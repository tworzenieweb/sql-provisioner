# SQL Provisioner

Simple CLI tool for validating SQL files before inserting them into DB.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

To use this library, you would need to have `composer` installed and have a table for storing already performed DB deploys.
Lastly, you would need to provide `.env` file with connection information and table and column name for checks

```
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
PROVISIONING_TABLE=changelog_database_deployments
PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN=deploy_script_number
```

### Installing

It installs just like any other composer project

```
composer require tworzenieweb/sql-provisioner
```

Then you should be able to run command from vendor/bin/sql-provisioner

```
vendor/bin/sql-provisioner /location/to/your/sql/files
```

### Demo

This is cinerama demo of usage

[![asciicast](https://asciinema.org/a/77kkwfpky9oio9i12ljwi3436.png)](http://asciinema.org/a/77kkwfpky9oio9i12ljwi3436)


## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

## Authors

* **Luke Adamczewski** - *Initial work* - [tworzenieweb](https://github.com/tworzenieweb)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details