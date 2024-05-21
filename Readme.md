
# Simple REST API

### Task Description:

Your task is to create simple REST API with two endpoints:
* /getMovie - User should be able to pass title of movie, year and version of plot to this endpoint. This endpoint should request results from IMDB Open API ( https://www.omdbapi.com/ ) and return to user.
* /getBook - User should be able to pass an ISBN number of book and get all possible information about the book. This endpoint should request results from OpenLibrary API( https://openlibrary.org )

All results should be returned in JSON format.

REST API should have implemented JWT web token as way of authorization of requests.

REST API can be done in one of the languages: C#, Java, PHP

Client for this REST API must be done in PHP. The only responsibility of client is to call endpoint and display results on the screen.

### Technical Requirements:
- PHP 7.x / C# / Java
- OOP paradigm used
