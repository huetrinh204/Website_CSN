# JFilters
The extension is offering filtering functionalities for the Joomla CMS.

With it's default configuration, generates filters from the Joomla's custom fields, categories and tags. 
But can be extended, in a very simple way, for filtering other database tables and used with other extensions.

It offers great flexibility both for the user and the developer, allowing him to extend the current logic, by using his/her own classes in the component's logic.
This is still a major impediment for the Joomla developer, which we are overcoming through a *Dependency Injection* mechanism (see: [Technical Principles](#techinical-principles)).

## Minimum Requirements
- Joomla 4

- PHP 7.2

- MySQL 5.6

## Specifications
### Filters Generation
Ability to create filters from any set of database tables for any extension. It is reading a configuration file (by default filters.xml), for generating the filters in the JFilters component.
Once the filters are generated, they can be used for filtering. This configuration file contains all the information needed for the generation of the filters and the filtering.

The declared filters in the configuration xml can be dynamic or static.
A dynamic filer, in the configuration, implies that several filters are generated from a database table (e.g. fields).
A static filter is single filter (e.g categories).

So if we intend to generate filters for other database tables, editing the configuration file can do the job, without touching a core file.
In case we want to modify the filters configuration file, we better create a new one under a new name/location and declare that through the JFilters component's configuration.
The JFilters component's configuration provides the option to declare the consumed configuration files, for the filters, the contexts and the classes preference files.


### Techinical Principles

1) Lazy Loading A.K.A Load when needed.
With that principle an object is generated only when requested. Typically the object's "optional" properties are generated, upon request as well.

2) Singletons. 
Any created object is not created again. The same applies for the object's properties, when they are immutable.

3) [SOLID](https://itnext.io/solid-principles-explanation-and-examples-715b975dcad4) principles.
To comply with the Dependency Inversion Principle, Interfaces are used as parameters where possible.

	A mechanism is created (ObjectManager) for substituting the Interfaces with the preferred classes on the runtime.
	Those preferred classes are declared, through a configuration file named: *preferences.php*.
 	That file contains a php array of the form *Interface => class*, where each class has to implement the relevant Interface.
 	Any developer is encouraged to create a new class and change the Interface preference in the *preferences.php*, if he/she intents to add extra functionality to a class function.
 	Though the component's configuration the *preferences.php* file can be overwritten, with one of the developer's preference.
 	This way the classes can be easily extended without editing core files and without the changes being overwritten after the extension's update.
 	
4) Class dependencies are declared as parameters in the class' *constructor*. This way any class can be tested, by mocking those dependencies.
	>Instantiating dependent classes within the class' functions is not recommended. 

5) The developer is encouraged to instantiate/get an object by using the *ObjectManager*. 
The *ObjectManager* automatically instantiates all the dependencies (either classes or Interfaces). 
Also it is a container, storing and re-using the singleton objects upon request.

6) Each type of element (e.g. a filter) is an object (Object Relational Mapping). A set of similar objects consist a collection.
Any colection is a singleton that implements the [Iteratoraggregate](https://www.php.net/manual/en/class.iteratoraggregate.php). 
The collection is agnostic to the underlying persistence layer (e.g. database) and provides all the functions for it's functionality (add/remove items, set conditions, ordering, etc.). 

7) The collection's Objects (e.g. a Filter), are usually instantiated and fed with data by their respective collection.
