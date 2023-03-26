# LarpingBase\LarpingBundle\Service\LarpingService  







## Methods

| Name | Description |
|------|-------------|
|[__construct](#larpingservice__construct)|The default construt for this clas|
|[calculateCharacter](#larpingservicecalculatecharacter)|Calculate the stats for a given chararacter|
|[setStyle](#larpingservicesetstyle)|Set symfony style in order to output to the console|
|[statsHandler](#larpingservicestatshandler)|Calculates the atribute when an characters is changed|




### LarpingService::__construct  

**Description**

```php
public __construct (\EntityManagerInterface $entityManager, \CacheService $cacheService)
```

The default construt for this clas 

 

**Parameters**

* `(\EntityManagerInterface) $entityManager`
: The entity manager  
* `(\CacheService) $cacheService`
: The cache service  

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


### LarpingService::setStyle  

**Description**

```php
public setStyle (\SymfonyStyle $io)
```

Set symfony style in order to output to the console 

 

**Parameters**

* `(\SymfonyStyle) $io`
: Symfony style\  

**Return Values**

`self`




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

