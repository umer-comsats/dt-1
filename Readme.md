File: BookController

The Good Parts:

Dependency Injection: The code utilizes dependency injection which makes it easier to manage dependencies, thus making the code cleaner and easier to test.
Code Modularity: The code is quite modular with separate methods for different functionalities which is a good practice as it makes the code easier to read and maintain.
DocBlocks: The use of DocBlocks for each method is good as it provides context and information about the method's purpose and the expected inputs.

The parts that can be improved:

Repeated Code: The code repeatedly retrieves all request data with $data = $request->all(); in several methods. If all the data from the request isn't needed, only the required data should be retrieved to ensure data integrity.
Hardcoding Role IDs: The roles (like Admin and Super Admin) are being retrieved using the env() helper. It would be better to use constants to retrieve these values for better maintainability.
Validation: There's no validation performed on the incoming request data. Before processing, the data should be validated according to the application's requirements.
Error Handling: The code lacks proper error handling. For example, the show() method should handle the case if $id is not found.
Use of array_except(): The array_except() helper function has been deprecated since Laravel 5.8. It is better to use the Arr::except() function or even better - specify only required fields.
Env Calls: Using env() function directly outside the configuration files is not recommended. If the config is cached, env calls will return null.
