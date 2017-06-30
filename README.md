[![Build Status](https://travis-ci.org/tworzenieweb/sql-provisioner.svg?branch=master)](https://travis-ci.org/tworzenieweb/sql-provisioner)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tworzenieweb/sql-provisioner/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tworzenieweb/sql-provisioner/?branch=master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/064d6b7a-544d-4f80-8f7b-e4ebfeeb3282/big.png)](https://insight.sensiolabs.com/projects/064d6b7a-544d-4f80-8f7b-e4ebfeeb3282)

# SQL Provisioner

Simple CLI tool for database changes deploying. It allows to review each dbdeploy file before executution.

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

### Using

The `vendor/bin/sql-provisioner [path-to-folder]` command will scan the content of [path-to-folder] directory.
 
The script will look for `.env` file containing connection information in format:
```
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
PROVISIONING_TABLE=changelog_database_deployments
PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN=deploy_script_number
```

If you want to create initial .env use ```--init```

```vendor/bin/sql-provisioner --init [path-to-folder]```

The next step is searching for sql files and trying to queue them in numerical order.
First n-th digits of a filename will be treated as candidate number. 
This will be used then to check in database if a certain file was already deployed (PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN).
Before the insert, it will print the formatted output of a file and result of internal syntax check.
Then you can either skip or execute each.

If you would like to skip already provisioned candidates use ```--skip-provisioned```
If you would like to skip syntax checking (for speed purpose) of candidates use ```--skip-syntax-check```

### Demo

This is cinerama demo of usage

[![asciicast](https://asciinema.org/a/77kkwfpky9oio9i12ljwi3436.png)](http://asciinema.org/a/77kkwfpky9oio9i12ljwi3436)


## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/tworzenieweb/sql-provisioner/releases).

## Authors

* **Luke Adamczewski** - *Initial work* - [tworzenieweb](https://github.com/tworzenieweb)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
