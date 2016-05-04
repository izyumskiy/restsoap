# restsoap
Package for generating REST and SOAP interfaces with WSDL

* Generates REST and SOAP interfaces with WSDL;
* XML or JSON input/output has been provided in REST scheme;
* HTTP codes in headers are returned in responses;
* Request/response validation by XSD-scheme in WSDL

# REST

## Resource call

Link to the resource should consist of several parts. 
For example, /api/rest/example/xml/books/{book_id}

Where:

    /api/rest/example/ - link to webmaster's resource library
    /xml/ - requested data format. Supported formats - xml, json
    /books/ - resource name. All resources are described in documentation followed by the input parameters

Input parameters

There are several ways to transfer parameters for GET requests:

    In URL - /api/rest/example/xml/books/{book_id}
    In GET-parameters - /api/rest/example/xml/books?book_id=351
    Mixed type - /api/rest/example/xml/books/{book_id}?author=dostoevsky

Multiple values of one parameter are transferred separated by commas. For example, geo=186,150,37.
Attention! Values of GET-parameters shall override the values of these same parameters in URL.

For PUT and POST requests parameters in HTTP-package body shall be transferred in the same order in which they are described in XSD-scheme. Presentation format of the parameters must match the type of the requested data - XML or JSON.
Check the example of PUT-request in XML and JSON formats:

    <updateBooksRequestData>
        <book_id>35</book_id>
		<title>New Title</title>
		<author>New Author</author>
		<price>100</price>
    </updateBooksRequestData>

    {
        "book_id": 35,
		"title": "New Title",
		"author": "New Author",
		"price": 100
    }

Response format

Regardless of the format of interaction with the API, the data structure contained in the methods' response is as follows:

    status - status of the query;
     
    Possible responses:
        200 - the query was successfully performed;
        400 - Incorrect incoming parameters;
        403 - Incorrect apiKey or insufficient access rights;
        500 - Internal error.
         
        This status code can be duplicated in The HTTP status code
     
    error - error message;
        If the query is successful, the value will be empty.
    data - array with the data returned for the method called. If the query fails it will be empty.

Example:

    {
        "status": 500,
        "error": "Server error",
        "data": [ ],
    }

The content of the data block is described in the WSDL/XSD.


ApiController is an enter point for API
