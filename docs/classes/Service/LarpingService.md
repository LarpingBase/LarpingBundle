# LarpingBase\LarpingBundle\Service\LarpingService  







## Methods

| Name | Description |
|------|-------------|
|[__construct](#larpingservice__construct)|The default construt for this clas|
|[calculateCharacter](#larpingservicecalculatecharacter)|Calculate the stats for a given chararacter|
|[getMarkdowCard](#larpingservicegetmarkdowcard)|Generates a markdown character table|
|[statsHandler](#larpingservicestatshandler)|Calculates the atribute when an characters is changed|




### LarpingService::__construct  

**Description**

```php
public __construct (\EntityManagerInterface $entityManager, \CacheService $cacheService, \LoggerInterface $pluginLogger)
```

The default construt for this clas 

 

**Parameters**

* `(\EntityManagerInterface) $entityManager`
: The entity manager  
* `(\CacheService) $cacheService`
: The cache service  
* `(\LoggerInterface) $pluginLogger`
: The Logger Interface  

**Return Values**

`void`


<hr />


### LarpingService::calculateCharacter  

**Description**

```php
public calculateCharacter (\ObjectEntity $character)
```

Calculate the stats for a given chararacter 

 

**Parameters**

* `(\ObjectEntity) $character`
: The charater to calculate for  

**Return Values**

`\ObjectEntity`




**Throws Exceptions**


`\Exception`


<hr />


### LarpingService::getMarkdowCard  

**Description**

```php
public getMarkdowCard (\ObjectEntity $character)
```

Generates a markdown character table 

 

**Parameters**

* `(\ObjectEntity) $character`
: the character to create the card for  

**Return Values**

`string`

> the card as markdown


<hr />


### LarpingService::statsHandler  

**Description**

```php
public statsHandler (array $data)
```

Calculates the atribute when an characters is changed 

 

**Parameters**

* `(array) $data`
: The data at the time of activaation of the action  

**Return Values**

`array`




<hr />

