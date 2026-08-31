# Importer-Tool

<img width="1919" height="893" alt="Importer-Tool-Screenshot" src="https://github.com/user-attachments/assets/c79259f8-ce8b-4896-8d56-e8ce36caa251" />


A simple importer tool for reading data from files of various types. The extracted data is saved in a MySQL database. Currently, the supported file types include CSV, XLS / XLSX, JSON and XML.

To be able to use the importer tool, the user must create an account. He can then upload a a file of his choice (as long as the type is supported) and start the import process.

Typical use case: an HR department receives employee data as spreadsheets or exports from other systems and needs it in a central database without entering it manually by hand.

As of now, the importer tool only works with files that contain flat, tabular data. The headers Name, Email, Role and IsActive must be present in each file, respective to its type:
- CSV files and Excel sheets must have a row containing the aforementioned headers. This row should sit at the top of the file
- JSON files must contain a top-level array of objects. Each object is one record and must use the same keys, ie. Name, Email, etc
- XML a single root element containing multiple 'person' child nodes. Each record's fields are child elements of person.

### Tech stack

Backend: Symfony (PHP)\
Frontend: JavaScript\
Templates: Twig\
Database: MySQL\
Testing: PHPUnit\

### Hosting

Currently the application is being hosted locally, and thus can only be accessed via localhost.
