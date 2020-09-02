var Osynapsy = Osynapsy || {'worker' : {}};

Osynapsy.worker.add = function(name, url)
{
    if (!window.Worker) {
        console.log('questo browser non supporta i worker');
    }
    var myWorker = new SharedWorker(url);

    // Get the proxy worker port for communication
    var myWorkerPort = myWorker.port;
    // Send a "hello" message to the worker
    myWorkerPort.postMessage( {type: 'hello', says: 'Hello worker !'} );
    myWorkerPort.onmessage = function( event )
    {
        var message = event.data;
        Osynapsy.notification(message.says);
    };
};

onconnect = function(event)
{
    var workerPort = event.ports[0];

    // Send a message to the worker proxy. The worker is up and running.
    workerPort.postMessage({type: 'connected', says: "I'm alive!"});
    
    workerPort.onmessage = function( event )
    {
        // Worker receives a message !
        // The `event.data` is what the worker proxy sends using `postMessage()`. Could be a String, Number or an Object type.
        // Here, `event.data` contains an object: `{type: String, says: String}`
        var message = event.data;
        switch( message.type )
        {
            // It's a hello world message
            case 'hello':
                console.log( '[RECEIVED BY WORKER] '+ message.says );
                // Reply to the worker proxy
                workerPort.postMessage( {type: 'hello', says: 'Hello proxy!'} );
                break;

            // It's a terminate message
            case 'close':
                console.log( '[RECEIVED BY WORKER] '+ message.says );
                // Reply to the worker proxy
                workerPort.postMessage( {type: 'close', says: 'I will be back!'} );
                // Close the worker
                console.log( '[WORKER] Worker stops' );
                close();
                break;

            // It's something else. Skip it.
            default:
                break;
        }
    };
};