#### Instructions
```
* clone the repo
* run composer install
* copy .env.example to .env
* run php artisan key:generate
* run php artisan serve
```

Navigate to the homepage and the csv will be processed and the json data will be displayed.

Test suite can be ran with **php artisan test** command

#### Troubleshooting
If there's an error regarding not having a database when you load the homepage, then change the .env values to

```
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

#### My Approach
With this project i've left the homepage as the place to receive the data. I wanted to be able to iterate quickly and felt
that it would be easy to just refresh the page and receive the json data. so the code can be found at routes/web.php.

I chose to just use vanilla php to load and stream the csv over a vendor package. This was mostly down to the simplicity 
of handling the data and choosing to spend that time on other areas instead of finding a package.

I decided to use a pipes and filters approach to handle the data. This allowed me to make the code more readable, 
testable, easier to maintain and scalable if there were any need for it in the future. It also gives me the ability to 
switch out an existing pipes with a new one if the way of handling it needs to be changed.

A DTO (app/Data/NameParserPipelineContainer.php) was used to pass the data through the pipeline, which stored the row 
data that was being streamed and another DTO (app/Data/Person.php) to handle the users data. I applied immutability when
moving data around in the pipeline as it's a good practice to avoid side effects.
DTO's were used to keep the code clean, readable and structured.

Here's information on how the pipes operate in order:
* app/Pipes/NormalizeString.php
  * Searches the row for **and** and changes it with **&**
* app/Pipes/SplitMultipleUsers.php
  * Explodes on **&** and creates an array of users.
  * Then create instances of the Person DTO for each entry and set the nameContents value, which is the string exploded 
    on whitespace to give the different parts of the name.
* app/Pipes/HandleTitle.php
  * Sets the Persons title by getting the first part of the nameContents array.
* app/Pipes/HandleFirstNameOrInitial.php
  * This pipe cycles through the users and checks how many parts are in the nameContents array. If there is more than 
    one part then it knows that there is the last name and another string available.
  * It then takes the next part of the nameContents array and runs a trim on it to remove a **.** character if it exists.
    As this would indicate that the part is an initial.
  * It tests against the length then. If the length is 1 then it sets the initial otherwise it sets the first name.
* app/Pipes/HandleLastName.php
  * Cycles through the users and checks the name parts to see if there's an entry left. If there is it sets it as the last
    name of that user.
  * If there are multiple users it then plucks the last name from the collection of Person's and cycles through them 
    again to set any person's last name that doesn't already have one.

#### AI Use
In this project i used AI to generate the tests. I did this at the end of the task after all the parsing functionality 
and structure had been put in place. The main reason for doing this was simply as a time saver as it would've taken much 
more time to write. 


#### Improvements
Overall i treated this as if it was a ticket that was a simple task that just needed a solution without really being 
overengineered. But saying that there are plenty of things i did consider and wished to note them down as potential 
improvements.

* Metadata could be added to give extra information. 
  * For example when a string is split up into multiple users, it could 
    be flagged up for human review to ensure the data is correct.
* Move the endpoint to an api route with a post method. 
  * Can add validation for the file then, for type, size, schema, etc
  * Run anti virus checks on the file.
  * Rate limit
* Asynchronous processing
  * I'd batch the data and process it in parallel
  * Running async may be useful if you have an SLO that relates to request response times
  * Helps prevent timeouts happening and hogging server resources
* Caching/Store that the CSV has been processed
  * If the csv's were always small, then could potentially cache the results to prevent it all being processed again if 
    somebody were to run the file unchanged.
  * Could also choose to store that this files hash has been processed in a database.
* Database integration
  * With that implementation i'd look to use models over the DTO objects.
* Add logging
* Pipeline could be customisable
  * Could split up the first name and initial stage in to separate pipes and the list of pipes to be processed could be 
    dynamic and choose the correct pipeline solution based on whether an initial is found in the string or not with regex
* Error reporting
  * In a live environment data could be malformed, would look to deal with and alert on errors

#### Other Considerations
Dr & Mrs Joe Bloggs was one that i thought about a bit. I decided that it was best to leave it so it was Dr Bloggs and 
Mrs Joe Bloggs. This is because i don't think the decision could be automated with absolute certainty that it isn't Mrs 
Joe Bloggs, due to names being able to be shortened. Joe is a more common male name and the formers name is often used 
when it's wrote as a man and wife's name. Frankly in this scenario i'd be hesitant to guess that it's meant for the former 
name in the string and not the latter.
With a situation like this i'd think about providing more meta data with the list and some variable to flag this up for 
a human review. I'd have some functionality to determine the confidence in whether a Persons values are accurate or not 
and then have thresholds to determine whether it should be flagged for human review.

Obviously this is meant to be a simple tech test with limited information. In a real world scenario i'd look to gather 
more information to the ticket or to verify this was exactly what was expected from the ticket before it went in to the 
sprint.

Names & user input can be very tricky to handle correctly. One thing i'd also have to consider if were to do further work 
is how to be confident that a single character is an initial and not the users name. As it is legal for users to have a 
single character as their name.
