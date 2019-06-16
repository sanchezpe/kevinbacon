<?php
/**
 * Pablo Sanchez
 * Description: PHP functions and variables shared between pages
 * Features: Establish connection with the movie database. Functions used to:
 * find an actor ID, list movies of actor, and list movies of actor with Kevin Bacon
 */

//Login info
$db = 'mysql:host=localhost;dbname=dbname';
$username = 'user';
$password = 'password';

//Connect to db
try {
    $movieDatabase = new PDO($db, $username, $password);
} catch (PDOException $e) {
    echo "<p>" . $e->getMessage() . "</p>";
}

/**
 * Receive the movie database name, the actor's first name, and the actor's
 * last name. Match the actor's first name and last name to the actor's ID.
 *
 * @param PDO $movieDatabase
 * @param String $firstName
 * @param String $lastName
 * @return Int actorID
 */
function findActorID($movieDatabase, $firstName, $lastName)
{
    /* 
    global $movieDatabase; DO NOT USE!! Not stable. Sometimes it cause error:
    Call to a member function query() on null.
    Solution: Parse variable to function instead.
    */

    //Select the actor's id, first name, and last name that match user input.
    //LIKE $firstname%: Values that start with $firstname.
    $query = "
            SELECT COUNT(a.id) AS count, a.id, a.first_name, a.last_name
            FROM actors a
            JOIN roles r ON r.actor_id = a.id
            JOIN movies m ON m.id = r.movie_id
            WHERE a.first_name LIKE '" . $firstName . "%" . "'
                AND a.last_name = '" . $lastName . "'
            GROUP BY a.id
            ORDER BY count DESC, a.last_name, a.first_name
            ";
    //print $query;

    //default value
    $actorID = null;

    try {
        foreach ($movieDatabase->query($query) as $row) {
            $actorID = $row["id"];
        }

        //close database connection
        $movieDatabase = null;

    } catch (Exception $e) {
        //print message in case of error
        echo "<p>" . $e->getMessage() . "</p>";
    }

    if ($actorID == null) {
        echo "<p>Actor " . $firstName . " " . $lastName . " does not exist in database.</p>";
    }

    //print $actorID;
    return $actorID;

}

/**
 * Receive the movie database name, the actor's first name, and the actor's
 * last name. Print a table with the movies of actor.
 *
 * @param PDO $movieDatabase
 * @param String $firstName
 * @param String $lastName
 */
function listMovies($movieDatabase, $firstName, $lastName)
{
    /* 
    global $movieDatabase; DO NOT USE!! Not stable. Sometimes it causes error:
    Call to a member function query() on null.
    Parse variable to function instead.
    */

    $actorID = findActorID($movieDatabase, $firstName, $lastName);

    //Select all columns from movies that match actor id.
    $query = "
            SELECT *
            FROM movies m
            JOIN roles r ON r.movie_id = m.id
            JOIN actors a ON a.id = r.actor_id
            WHERE a.id = '" . $actorID . "'
            ORDER BY year DESC, m.name
    ";
    //print $query;

    //Verify no null value is parsed
    if ($actorID != null) {
        try {

            ?>
            <p>Films with <?= $_GET["firstname"] ?> <?= $_GET["lastname"] ?> </p>

            <table>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
                <?php

                $count = 0;

                foreach ($movieDatabase->query($query) as $row) {
                    $count++;
                    ?>
                    <tr>
                        <td><?= $count ?></td>
                        <td><?= $row["name"] ?></td>
                        <td><?= $row["year"] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php

            //close database connection
            $movieDatabase = null;

        } catch (Exception $e) {
            //print message in case of error
            echo "<p>" . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Actor " . $firstName . " " . $lastName . " not found.</p>";
    }

}

/**
 * Receive the movie database name, the actor's first name, and the actor's
 * last name. Print a table with movies of actor with Kevin Bacon.
 *
 * @param PDO $movieDatabase
 * @param String $firstName
 * @param String $lastName
 */
function listMoviesWithKevin($movieDatabase, $firstName, $lastName)
{
    /* 
    global $movieDatabase; DO NOT USE!! Not stable. Sometimes it causes error:
    Call to a member function query() on null.
    Parse variable to function instead.
    */

    $actorID = findActorID($movieDatabase, $firstName, $lastName);
    //print $actorID;

    //Select movies that match actor ID
    $query = "
            SELECT m.*
            FROM actors a
            JOIN actors kevin
            JOIN roles r1 ON r1.actor_id = a.id
            JOIN roles r2 ON r2.actor_id = kevin.id
            JOIN movies m ON m.id = r1.movie_id AND m.id = r2.movie_id
            WHERE a.id = '" . $actorID . "'
                AND kevin.first_name = 'Kevin'
                AND kevin.last_name = 'Bacon'
            ORDER BY year DESC, m.name
            ";
    //print $query;


    //Return Undefined if user input Kevin Bacon
    if ($actorID == findActorID($movieDatabase, "kevin", "bacon")) {
        echo "<p>Undefined</p>";
    } //$movieDatabase->query($query)->fetchColumn()> 0: Check for empty results
    elseif ($actorID != null && $movieDatabase->query($query)->rowCount() > 0) {
        try {
            ?>
            <p>Films with <?= $_GET["firstname"] ?> <?= $_GET["lastname"] ?> and Kevin Bacon</p>

            <table>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
                <?php
                $count = 0;
                //alternative loop:
                foreach ($movieDatabase->query($query) as $row) {
                    $count++;
                    ?>
                    <tr>
                        <td><?= $count ?></td>
                        <td><?= $row["name"] ?></td>
                        <td><?= $row["year"] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php

            //close database connection
            $movieDatabase = null;

        } catch (Exception $e) {
            //print message in case of error
            echo "<p>" . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Actor " . $firstName . " " . $lastName . " wasn't in any films with Kevin Bacon.</p>";
    }
}

?>