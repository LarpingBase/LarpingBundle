# LarpingBase\LarpingBundle\Service\InstallationService  



## Implements:
CommonGateway\CoreBundle\Installer\InstallerInterface



## Methods

| Name | Description |
|------|-------------|
|[__construct](#installationservice__construct)|The constructor|
|[checkDataConsistency](#installationservicecheckdataconsistency)|The actual code run on update and installation of this bundle|
|[install](#installationserviceinstall)|Every installation service should implement an install function|
|[uninstall](#installationserviceuninstall)|Every installation service should implement an uninstall function|
|[update](#installationserviceupdate)|Every installation service should implement an update function|




### InstallationService::__construct  

**Description**

```php
public __construct (\EntityManagerInterface $entityManager)
```

The constructor 

 

**Parameters**

* `(\EntityManagerInterface) $entityManager`
: The entity manager  

**Return Values**

`void`


<hr />


### InstallationService::checkDataConsistency  

**Description**

```php
public checkDataConsistency (void)
```

The actual code run on update and installation of this bundle 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`




<hr />


### InstallationService::install  

**Description**

```php
public install (void)
```

Every installation service should implement an install function 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`




<hr />


### InstallationService::uninstall  

**Description**

```php
public uninstall (void)
```

Every installation service should implement an uninstall function 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`




<hr />


### InstallationService::update  

**Description**

```php
public update (void)
```

Every installation service should implement an update function 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`




<hr />

