<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/5.0/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;
    var pusher = new Pusher("14c23ec2e49bbd759476", {
      cluster: 'ap1',
      forceTLS: true
    });

    var channel = pusher.subscribe('my-channel');
    channel.bind('my-event', function(data) {
      alert(JSON.stringify(data));
    });
  </script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Intenta publicar un evento en el canal <code>mi-canal</code>
    con nombre del evento <code>mi-evento</code>.
  </p>

  <ul id="myList">
    <li>Primer mensaje</li>
  </ul>

</body>