<?php
if ($_POST)
{
    $errors = array();

    if (!isset($_POST['quiz-title']) || empty($_POST['quiz-title']))
        $errors[] = 'No quiz title inserted';

    if (isset($_POST['quiz-title']) && !valid_postdata($_POST['quiz-title'], true))
        $errors[] = 'Quiz title is not alpha numeric';

    if (!isset($_POST['quiz-desc']) || empty($_POST['quiz-desc']))
        $errors[] = 'No quiz description inserted';

    if (isset($_POST['quiz-desc']) && !valid_postdata($_POST['quiz-desc']))
        $errors[] = 'Quiz description is not alpha numeric';

    if (!isset($_POST['quiz-author']) || empty($_POST['quiz-author']))
        $errors[] = 'No quiz author inserted';

    if (isset($_POST['quiz-author']) && !valid_postdata($_POST['quiz-author'], true))
        $errors[] = 'Quiz author is not alpha numeric';

    $question_count = 0;
    $questions = array();

    while ($question_count++ < 100)
    {
        if (!isset($_POST['question_' . $question_count]) || empty($_POST['question_' . $question_count])
            || !isset($_POST['answer_' . $question_count]) || empty($_POST['answer_' . $question_count])
            || !isset($_POST['points_' . $question_count]) || empty($_POST['points_' . $question_count]))
            break;

        if (isset($_POST['question_' . $question_count]) && !valid_postdata($_POST['question_' . $question_count]))
            $errors[] = 'Quiz Question ' . $question_count . ' is not alpha numeric';

        if (isset($_POST['answer_' . $question_count]) && !valid_postdata($_POST['answer_' . $question_count]))
            $errors[] = 'Quiz Answer ' . $question_count . ' is not alpha numeric';

        if (!isset($_POST['points_' . $question_count]) || ((int) $_POST['points_' . $question_count]) < 0)
            $errors[] = 'Quiz Points Amount ' . $question_count . ' is not a positive numeric';

        if (count($errors))
            break;

        $ans = explode(';', $_POST['answer_' . $question_count]);
        $questions[] = array(
            'question' => $_POST['question_' . $question_count],
            'answers' => $ans,
            'points' => isset($_POST['points_' . $question_count]) ? (int) $_POST['points_' . $question_count] : 1
        );
    }

    if (!$errors)
    {
        $form_submitted = true;
        $form_name = 'quiz-' . str_replace(' ', '_', $_POST['quiz-author']) . '-' . str_replace(' ', '_', $_POST['quiz-title']) . '.json';
        $form_location = 'raw/' . $form_name;

        if (file_exists($form_location))
            $errors[] = "File '$form_location' already exists. Please choose a different quiz name.";
        else
        {
            $json = json_encode(array(
                str_replace(' ', '_', $_POST['quiz-title']) => array(
                    'name' => $_POST['quiz-desc'],
                    'author' => $_POST['quiz-author'],
                    'procedure' => $question_count - 1,
                    'time_unix' => time(),
                    'time_string' => date('r'),

                    'questions' => $questions
                )
            ));

            file_put_contents($form_location, $json);
        }
    }
}

if (isset($form_submitted) && $form_submitted && file_exists($form_location))
{
    header('Location: ' . $form_location);
}

function parse_postdata_if_exists($item)
{
    if (isset($_POST[$item]) && !empty($_POST[$item]))
    {
        $content = addslashes(htmlentities($_POST[$item], ENT_QUOTES));
        return 'value="' . $content . '" ';
    }

    return '';
}

function quiz_question_box($question_no)
{
    if (!is_int($question_no))
        return;

    $pd_q = parse_postdata_if_exists('question_' . $question_no);
    $pd_a = parse_postdata_if_exists('answer_' . $question_no);
    $pd_p = parse_postdata_if_exists('points_' . $question_no);
    $pd_p = ($pd_p == '' ? $pd_p = 'value="1"' : $pd_p);

    echo <<<HTML
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-5">
        <input name="question_{$question_no}" class="form-control" type="text" placeholder="Your Question (#{$question_no})" {$pd_q} />
    </div>

    <div class="col-sm-3">
        <input name="answer_{$question_no}" class="form-control" type="text" placeholder="Semicolon separated answers (#{$question_no})" {$pd_a} />
    </div>
    <div class="col-sm-1">
        <input name="points_{$question_no}" class="form-control" type="number" {$pd_p} />
    </div>
</div>
<br />
HTML;
}

function valid_postdata($data, $is_title = false)
{
    $allowed_chars = null;

    if (!$is_title) {
        $allowed_chars = array(';', '?', ' ', '.', '!', '\'', ',', '+', '-', '*', '&', '(', ')', '%', '^', '$', '#', '@', ':');
    } else {
        $allowed_chars = array(';', '?', ' ');
    }

    $repl = '';

    return ctype_alnum(str_replace($allowed_chars, $repl, $data));
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Quiz Generator</title>
        <meta name="description" content="Simple Quiz Generator">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="/css/normalize.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/bootstrap.min.css">

        <script src="/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title">Quiz Generator</h1>
                <nav>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/raw">Browse</a></li>
                    </ul>
                </nav>
            </header>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-sm-offset-2 col-sm-8 text-center">
                    <h1>Generate quizzes in JSON format easily</h1>
                    <p>Simply add questions and their respective answers in the boxes, and press submit once you're done!</p>
                    <p>Quite a few characters are not allowed for security reasons, please stick to alpha numeric and basic symbols.</p>
                    <p>Fill in the form, press submit when done. If you plan to have a quiz with more questions, <a href="/?question_amount=<?php echo (isset($_GET['question_amount']) ? (int) $_GET['question_amount'] + 25 : 25) ?>">click here</a>.</p>

                    <h2>Let's make a quiz!</h2>
                </div>
            </div>
        </div>
    
        <div class="container">
            <div class="row">
                <form method="POST" action="index.php" class="form-horizontal" role="form">
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <input name="quiz-title" class="form-control" type="text" placeholder="Name your quiz!" <?php echo parse_postdata_if_exists('quiz-title') ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <input name="quiz-desc" class="form-control" type="text" placeholder="Brief description" <?php echo parse_postdata_if_exists('quiz-desc') ?> />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <input name="quiz-author" class="form-control" type="text" placeholder="Author!" <?php echo parse_postdata_if_exists('quiz-author') ?> />
                        </div>
                    </div>
                    <br />

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6 text-center">
                            <h3>Questions!</h3>
                            <p>Separate different possible answers by using a semi colon (;), amount of points for an answer is 1 by default.</p>
                        </div>
                        <br />

                        <?php
                            if (!isset($_GET['question_amount']))
                                $question_amount = 10;
                            else
                                $question_amount = (int) $_GET['question_amount'];

                            if ($question_amount < 5)
                                $question_amount = 10;

                            if ($question_amount > 50)
                                $question_amount = 50;

                            for ($i = 1; $i <= $question_amount; $i++)
                            {
                                quiz_question_box($i);
                            }
                        ?>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6 text-center">
                            <button type="submit" class="btn btn-lg btn-success">Submit Quiz!</button>
                        </div>
                    </div>
                </form>
                <br />
            </div>
        </div>

        <?php
            if (isset($errors) && count($errors))
            {
                $ermsg = '';
                foreach ($errors as $e)
                {
                    $ermsg .= '<p class="warning">' . $e . '</p>';
                }

                echo '<div class="row"><div class="col-md-offset-3 col-md-6"><h2>Errors when submitting form</h2>' . $ermsg . '</div></div>';
            }
        ?>

        <div class="footer-container">
            <footer class="wrapper">
                <h3>&copy; zarth.us <?php echo date('Y') ?></h3>
                <p>Made using bootstrap, html5boilerplate css, and ♥</p>
            </footer>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>

        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-55884511-1', 'auto');
            ga('send', 'pageview');
        </script>
    </body>
</html>
