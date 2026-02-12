# Homeowner Names - Technical Test

Alongside this brief, you should have been given a file containing a list of names. This list is an example of the sort of data we can sometimes be working with - it’s some fake homeowners who might be interacting with one of our agents.

In this example, the agent has been entering multiple people’s names into a single field in their old system, meaning that each row can potentially refer to one or more people.

We’d like to convert this list into individual people records, with the following schema:

- **title** - required
- **first_name** - optional
- **initial** - optional
- **last_name** - required

To complete this test, please write an application that can accept the CSV and output the list as a set of individual people, splitting the name into the correct fields, and converting into multiple people where necessary. This list should be returned in JSON format.

We do not expect you to handle any cases other than the ones given in the example csv to pass the requirements.

You may choose to create a full laravel application with a basic upload front end, a console application, or a simple class that loads the CSV from the filesystem. Data storage is not required - we’re happy to have stateless applications that return the data on request.

*We do not expect people to spend more than around 2 hours on this task.*


## Example Output

```json
[
  {
    "title": "Mr",
    "first_name": "John",
    "last_name": "Smith",
    "initial": null,
  },
  {
    "title": "Mrs",
    "first_name": "Jane",
    "last_name": "Smith",
    "initial": null,
  },
  {
    "title": "Mr",
    "first_name": "Tom",
    "last_name": "Staff",
    "initial": null
  },
  {
    "title": "Mr",
    "first_name": "John",
    "last_name": "Doe",
    "initial": null,
  },
  {
    "title": "Mr",
    "first_name": null,
    "last_name": "Fredrickson",
    "initial": "F"
  }
]
```

## How to Submit

To submit, please send a link to a github (or other publicly hosted) repository containing your submission. If you do not want to make this repository public, you can add the user `street-group-tech-review` as a collaborator to your project. 

## Questions

### Can I use external libraries?
Yes - we're happy for you to use anything that makes this simpler. We would ask that the core
logic around handing and splitting names is provided as bespoke code.

### What tooling can I use?
Similar to the above, we want this exercise to be a realistic reflection of how you'd work in a normal,
day-to-day environment. To that end, treat it as you would any other piece of work - feel free to google, slack overflow, etc.

### What about AI?
As a company, we're keen to adopt the latest technology in our work, and this does include LLMs and tools such as cursor or claude code. As above, if these are tools you use in your normal work, feel free to use them here. We do have a few stipulations regarding AI:

* If you do choose to use AI, please avoid using AI to generate your core parsing logic. Writing this part of the submission by hand will give us the best indication of your technical ability and give you the best chance of success.
* If you have used it, please disclose this in your submission. Either in the readme, or when sending the link over to your talent partner.
* Tell us _how_ you used it. We'd love to have a conversation about your working practices - it's an emerging field and it's usually a really interesting chat.
* Make sure you can explain and reason around what the code is doing - we treat any submission as if it were written by the submitter, and part of our interview process will include discussion of the approach and structure.

### How will you assess this?
One of our engineers will assess each and every submission we receive, and provide feedback whether or not the application is successful. We assess each submission on a few key areas:

* Does it meet the requirements of the exercise as written
* Does it follow general good practice and coding standards?
* How is it organised structurally, and is the logic clear and understandable?
* Is it easy to get up and running and assess?



