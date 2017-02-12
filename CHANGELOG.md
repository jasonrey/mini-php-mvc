# v2.0.0

- Not backwards compatible with v1.0
- Use of namespace
- Added class autoloader
- Removed all utilities method in subloading classes
- Table::$tablename is now a mandatory static variable
- Table::$primarykey is now a static variable, and accepts array for multiple primary key
- Table::$activedb is now a static variable
- Table columns is now defined in Table::$columns along with column type
- Database now uses PDO
- Updated Config::$dbconfig structure for the new Database library
- Removed View::$viewname
- Added Pug support as view renderer
- Removed inline `less.min.js` CSS renderer
- Removed LESS files
- Added basic Bower dependency
- Added basic gulpfile for development

# v1.0.2 - 2016-07-25

- Fixed page title not referenced in template
- Fixed Facebook meta tags support

# v1.0.1 - 2016-07-20

- Removed ES6 references
- Removed NodeJS references
- Fixed JavaScript inclusion coding typo

# v1.0.0 - 2016-04-13

- Version 1 release
