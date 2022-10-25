# Things of note

## Repeating a lot of code

-   Each if could be move to a function call with params for type
-   checking path (fixed by routing issue above)

## Unsafe code

-   Pages route -> explode via '/' and get the query param via array position - Is this not allowed to be a query param (it should be right?)
-   `$term` is passed even if condition is false

## HTTP status codes

-   We could use some response codes to semantically notify the client of what has happened

### Routing

-   Could be using

```
#[Route('/path_here/'{parm_here})]
function doSomething()
```

-   I think we need a routing layer (routes.php) to handle all of the apps routes - each route would have a matching controller class to accommodate.
-   This would allow us to read the route clearly and see the controller attached - easy to find all the related code.
-   Would also separate our logic nicely - don't want unrelated code living together.

### Validation layer

-   For ease of use this could be done in controller class related to route
-   maybe some generic functions like: trim(), stripslashes(), etc

### Logging layer

-   Would be nice to abstract the logging out into its own class

### Data layer

-   We want to separate the code that accesses the database from the business logic and the routes

### Base controller

-   We want to have a high level controller that can store generic functions that are used all over the app. e.g. logging, responding to client, auth, session
