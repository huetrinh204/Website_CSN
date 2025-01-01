# Scope
The generator classes generate the filters ready to be saved in our database table *#__jfilters_filters*.

## Types of generators
* **Declarative**
In a simple scenario, it generates a single filter by reading a configuration (_Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface_).
The filter's definition properties (e.g. id, name, context) are provided through the configuration.
Think of it, like all the values of a db table (e.g. *#_categories*) belong to a single filter. In case the values are in multiple-languages, it generates a filter for each language.

* **Dynamic**
Such a generator, can create several filters from a configuration (_Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface_).
Each filter's definition properties (e.g. id, name, context) are read from a database table. Hence several rows (i.e. filters) can be generated through this one.
The values table can contain values belonging to different filters (e.g. fields).
