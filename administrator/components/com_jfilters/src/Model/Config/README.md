# Scope
The Config objects, provide the necessary foundation, based on which the component works.

## Type of Configurations
At the moment there are 2 diffent types of configuration.
1. **Filter**. Implements the _FilterInterface_ and is used to provide the necessary variables for the generation and function of each type of filter.
2. **Context** Implements the _ContextInterface_ and is used to provide the necessary variables for each context.

** A context represents a type of results data in the application.

### Configuration Sections
Each configuration is divided into sections (implementing _SectionInterface_). Each section can be fetched from the relevant class/interface (see above) by it's name.

#### Section Fields
Each section is divided into fields (_Field_). A Field has it's own attributes as well as a name and a value.

** When the fields are created by our xmls, each xml's inner node represents a new Field. Each node attribute, a Field's attribute and the node's value, the Field's value.

