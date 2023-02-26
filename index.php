<!--
Name:   Brandon Mitchell
Description:    A game of Tic-Tac-Toe implemented using HTML and PHP.  To keep
                track of the state, we use the query string.  This makes it 
                easy for someone to cheat, so the referer ID is checked.  The 
                computer does a turn first and is represented by X.  The player
                can then click a link to place their O.
How to Play:    Place in a folder in the www folder of your ampps installation.
                Go to localhost/foldername in a broswer while ampps is running,
                and you should see the app running.
-->

<!-- This header is always shown so the user knows the rules and can start a new game -->
<!DOCTYPE html>
<html>
<head>
    <title>Tic-Tac-Toe!</title>
</head>
<body>
    <h1><i>Tic-Tac-Toe Online!</i></h1>
    <p>Enjoy some Tic-Tac-Toe online whenever you please!  The computer is <br>
       the Xs and will make the first move.  You are the Os.  The ?s represent <br>
       open spaces and clicking on one will place an O there!  Standard Tic-<br>
       Tac-Toe rules apply!  Good luck!</p>
    <p>Start a <a href="/tictactoe">New Game</a> or continue the one below.</p>
    <hr/>
</body>
</html>

<?php

/**
 * Uses the "HTTP_REFERER" value to determine if the player modifed the URL and return a bool indicating so
 *
 * @return  bool, true if the user cheated, false otherwise
 */
function isCheater()
{
    // If the query string is not empty (so the game has started) ...
    if (!empty($_SERVER["QUERY_STRING"]))
    {
        // ... and the referer isn't set ...
        if (!isset($_SERVER["HTTP_REFERER"]))
        {
            // ... then the user cheated
            return true;
        }
    }
    
    return false;
}



/**
 * Returns the boardState by retrieving it from the server's query string or a blank board
 *
 * @return  string representing the board with the computer's turn made
 */
function getBoardState()
{
	// Check for empty instead of isset as if there is no query string, it is 
	// simply set to an empty string instead
    if (!empty($_SERVER["QUERY_STRING"]))
    {
        return $_SERVER["QUERY_STRING"];
    }

    // Empty representation of board
    return "?????????";
}



/**
 * Verifies the board is valid and returns a bool stating as such
 *
 * @param  boardState  string, represents the state of the board with X, O and ?
 * @return  bool representing if the board is valid or not
 */
function isValidBoard($boardState)
{
    // Ensure board is only X, O, and ? of the correct length
    // preg_match returns an int or false, so success is a 1, convert to bool
    $correctForm = preg_match('/[XO?]{9}/', $boardState) == 1;
    
    // A properly formed board will have same number of X and Os (at least when I read it in)
    $correctPlays = substr_count($boardState, 'X') == substr_count($boardState, 'O');
    
    return $correctForm && $correctPlays;
}



/**
 * Returns the boardState after the computer has made their move, move is made randomly
 *
 * @param  boardState  string, represents the state of the board with X, O and ?
 * @return  string representing the board with the computer's turn made
 */
function computerTurn($boardState)
{
    $openPlaces = array();
    
    for ($i = 0; $i < 9; $i++)
    {
        if (strcmp($boardState[$i], '?') == 0)
        {
            // Append to the end of the array
            $openPlaces[] = $i;
        }   
    }
    
    // Choose an open spot at random for our computer
    $boardState[$openPlaces[array_rand($openPlaces)]] = 'X';

    return $boardState;
}



/**
 * Returns a string with HTML in it that represents the board, string is later echoed to screen
 *
 * @param  boardState  string, represents the state of the board with X, O and ?
 * @param  gameOver  bool, defaults to false, indicate if the game is over so links for ? aren't drawn
 * @return  string representing the board with HTML tags in it
 */
function createFormattedBoard($boardState, $gameOver = false)
{
    // h1 makes the text big and pre makes the text monospaced so the spots 
    // line up nicely
    $boardStr = "<h1><pre>";
    
    for ($i = 0; $i < 9; $i++)
    {
        $place = $boardState[$i];
        
        // If the game has been won, don't generate the links for empty spaces
        if (strcmp($place, '?') == 0 && !$gameOver)
        {
            $urlStr = $boardState;
            $urlStr[$i] = 'O';
            $place = "<a href='/tictactoe?$urlStr'>?</a>";
        }
        
		// Split the boardState into three rows
        if ($i % 3 == 0)
        {
            $boardStr .= "<br>";
        }
        
        $boardStr .= $place;
    }
    
    $boardStr .= "</pre></h1>";
    
    return $boardStr;
}



/**
 * Returns a bool indicating if the player in question has won
 *
 * @param  boardState  string, represents the state of the board with X, O and ?
 * @param  player  char, indicate the player being checked, X or O
 * @return  bool representing if the player has won based on the boardstate
 */
function isWinner($boardState, $player)
{
    // Impossible for there to be a win if less than three X or O
    if (substr_count($boardState, $player) < 3)
    {
        return false;
    }
    
    // Store the possible win states and check them below
    $winStates = array
    (
        array(0, 1, 2), array(3, 4, 5), array(6, 7, 8),     // Horizontal
        array(0, 3, 6), array(1, 4, 7), array(2, 5, 8),     // Vertical
        array(0, 4, 8), array(2, 4, 6)                      // Diagonal
    );

    foreach ($winStates as $winState)
    {
        if ($boardState[$winState[0]] == $player &&
            $boardState[$winState[1]] == $player &&
            $boardState[$winState[2]] == $player)
        {
            return true;
        }
    }
    
    // If all combos were checked and it got here, then they haven't won
    return false;
}



/**
 * Plays a game of Tic-Tac-Toe with the computer's turn and then player's turn each page reload
 */
function game()
{
    if (isCheater())
    {
        echo "Cheater!  <a href='/tictactoe'>Start over?</a>";
        return;
    }

    // Get board and check if previous player move was a win
    $boardState = getBoardState();
    
    // This valid check isn't really necessary as the player can't submit their 
    // own board to the backend due to the referrer check
    if (!isValidBoard($boardState))
    {
        echo "Invalid board.  <a href='/tictactoe'>Start over?</a>";
        return;
    }
    
    if (isWinner($boardState, 'O'))
    {
        $newBoardStr = createFormattedBoard($boardState, true);
        echo $newBoardStr;
        echo "<br>The player has won!  <a href='/tictactoe'>Play again?</a>";
        return;
    }

    // Let the computer have a turn and then see if it won
    $boardState = computerTurn($boardState);
    if (isWinner($boardState, 'X'))
    {
        $newBoardStr = createFormattedBoard($boardState, true);
        echo $newBoardStr;
        echo "<br>The computer has won!  <a href='/tictactoe'>Play again?</a>";
        return;
    }

    // If it got past the previous two ifs, then there is no winner yet
    $newBoardStr = createFormattedBoard($boardState, false);
    echo $newBoardStr;

    // No more open spaces means that it is a tie
    if (substr_count($boardState, '?') == 0)
    {
        echo "<br>Cat's game, meow!  <a href='/tictactoe'>Play again?</a>";
    }
}



game();

?>