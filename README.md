# Apache Tika for TYPO3

[![Build Status](https://github.com/TYPO3-Solr/ext-tika/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/TYPO3-Solr/ext-tika/actions?query=branch:master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TYPO3-Solr/ext-tika/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TYPO3-Solr/ext-tika/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/TYPO3-Solr/ext-tika/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/TYPO3-Solr/ext-tika/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/apache-solr-for-typo3/tika/v/stable)](https://packagist.org/packages/apache-solr-for-typo3/tika)
[![License](https://poser.pugx.org/apache-solr-for-typo3/tika/license)](https://packagist.org/packages/apache-solr-for-typo3/tika)
[![Monthly Downloads](https://poser.pugx.org/apache-solr-for-typo3/tika/d/monthly)](https://packagist.org/packages/apache-solr-for-typo3/tika)

A TYPO3 CMS extension that provides Apache Tika functionality including

* text extraction
* meta data extraction
* language detection (from strings or files)

Tika can be used as standalone Tika app/jar, Tika server, and via SolrCell integrated in Apache Solr.

We're open for [contributions](#Contributions) !

Please find further information regarding Apache Tika on the [project's homepage](http://tika.apache.org)


## Continuous Integration

We use GitHub Actions for continuous integration.

To run the test suite locally, please use our DDEV docker environment https://github.com/TYPO3-Solr/solr-ddev-site.

**Note**:
  This requires a proper combination of branches:
* solr-ddev-site on master branch:
  * packages/ext-solr on master
  * packages/ext-tika on master
* solr-ddev-site on release-11.0.x branch
    * packages/ext-solr on release-11.0.x
    * packages/ext-tika on release-6.0.x
* Please refer to [version matrix](https://raw.githubusercontent.com/TYPO3-Solr/ext-solr/master/Documentation/Appendix/VersionMatrix.rst) for proper combination of branches

```shell
ddev enable tika
ddev tests-unit-tika
ddev tests-integration-tika
```

## <a name="Contributions"></a>Contributions

1. Fork the repository
2. Clone repository
3. Create a new branch
4. Make your changes
5. Commit your changes to your fork. In your commit message refer to the issue number if there is already one, e.g. `[BUGFIX] short description of fix (resolves #4711)`
6. Submit a Pull Request (here are some hints on [How to write the perfect pull request](https://github.com/blog/1943-how-to-write-the-perfect-pull-request))

### Keep your fork in sync with original repository

1. git remote add upstream https://github.com/TYPO3-Solr/ext-tika.git
2. git fetch upstream
3. git checkout master
4. git merge upstream/master
5. git push origin master
