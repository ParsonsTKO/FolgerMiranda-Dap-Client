# FOLGER DAP Client

## How do I get set up?

### Install all local requirements

```bash
make requirements
```

### Installation

```bash
make
```

### Execute tests

#### All test

```bash
make test
```

#### Behat tests

```bash
make behat
```

#### Codeception tests

```bash
make codecept
```

### Open your environment

```bash
make open
```

### See all commands available

```bash
make help
```

### Override configurations

Create the file `.env` with the environment variables, use the `.env.dist` file for reference.

## Contribution guidelines

- Writing tests
- Code review
- Other guidelines

## Who do I talk to?

- Repo owner or admin
- Other community or team contact

## Release your stable changes to ECR registry

### Using semantic release

Make sure all your changes are commited to the stable branch (currently `production` branch). This project uses Semantic release to automatically tag a release following the semantic versioning convenstions, then build and push the the Docker images to the Production registry. You should use your deployment tool to deploy the Docker image for this release.

Set your AWS credentials and then execute the `publish` command

```bash
make publish
```

## Publish your preview changes to ECR registry

Set your AWS credentials and then execute `publish` command. To push th current branch to ECR execute the following comamnd:

```bash
make review
```
