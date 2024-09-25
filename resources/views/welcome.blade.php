<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hey Kim</title>
</head>

<body>
    <form action="send" method="POST">
        @csrf
        <input type="text" name="nom" placeholder="Ton nom mec">
        <input type="number" name="total" placeholder="Montant">

        <input type="submit" value="Send it ">

    </form>


</body>

</html>
