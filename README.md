# LarpingBundle
An example repository for creating php symfony flex bundles

Used to test versioning

## Installation

Install the commengateway conform it installation manual

Make a folder on you computer, go to that folder trough CLI

```cli
git clone https://github.com/ConductionNL/commonground-gateway
```

then

```
cd commonground-gateway
```

then

```cli
git checkout feature/larping
```

Then

```cli
docker compose build
```


Then

```cli
docker compose up
```

Then 

```cli
docker compose exec php composer require larping-base/larping-bundle
```

And then

```cli
docker compose exec php bin/console commongateway:composer:upgrade
```
