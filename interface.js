// Wait for the DOM to finish loading before setting up the interface
document.addEventListener('DOMContentLoaded', function () {
    // Get references to the interface elements
    const resources = document.querySelector('#resources');
    const httpMethod = document.querySelector('#http-method');
    const resource = document.querySelector('#resource');
    const requestBody = document.querySelector('#request-body');
    const sendRequest = document.querySelector('#send-request');
    const statusCode = document.querySelector('#status-code');
    const responseBody = document.querySelector('#response-body');
    const clear = document.querySelector('#clear');

    // Add event listener to the resources list to update the resource input field
    resources.addEventListener('click', function (event) {
        event.preventDefault();
        resource.value = event.target.dataset.resource;
    });

    // Add event listener to the send request button to send a request to the web service
    sendRequest.addEventListener('click', function (event) {
        event.preventDefault();

        // Get the HTTP method and resource from the interface
        const method = httpMethod.value;
        const url = resource.value;

        // Prepare the request object based on the HTTP method and request body (if provided)
        const request = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        if (requestBody.value) {
            request.body = requestBody.value;
        }
        // Send the request to the web service using the Fetch API
        fetch(request)
            .then(function (response) {
                // Display the HTTP status code in the interface
                statusCode.textContent = `${response.status} ${response.statusText}`;

                // Parse the response body as JSON and display it in the interface
                response.json().then(function (data) {
                    responseBody.textContent = JSON.stringify(data);
                });
            })
            .catch(function (error) {
                console.error(error);
            });

        // Define the function to handle the response from the web service
        function handleResponse(xhr) {
            // Get the status code and response body
            let status = xhr.status;
            let responseBody = xhr.responseText;

            // Display the status code and response body
            document.getElementById('status-code').innerText = status;
            document.getElementById('response-body').innerText = responseBody;
        }



        // Define the function to clear the interface
        function clearInterface() {
            document.getElementById('status-code').innerText = '';
            document.getElementById('response-body').innerText = '';
        }

        // Attach event listeners to the button elements
        document.getElementById('send-request').addEventListener('click', sendRequest);
        document.getElementById('clear-interface').addEventListener('click', clearInterface);
    })
})
