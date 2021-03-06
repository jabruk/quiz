<?php

namespace Quiz\Controllers;

use Laminas\Db\TableGateway\TableGateway;

class Tests
{
    

    public function run()
    {   $adapter = \Quiz\Service\DB::getAdapter();
        $table = new TableGateway('tests', $adapter);
        $stmt = $table->select();

        $data = [];
        foreach($stmt as $current){
            $data [] = (array)$current;
        }

        $view = new \Quiz\View\Tests();
        $view->render([
            'title' => 'Tests',
            'data' => $data,
        ]);
    }

    public function runAdd()
    {
        $validator = $this->getValidator();

        if ($_POST && $validator->check($_POST)) {

            $adapter = \Quiz\Service\DB::getAdapter();
            $table = new TableGateway('tests', $adapter);
            $stmt = $table->insert(['name' => $_POST['name']]);
            
            $table = new TableGateway('tests', $adapter);
            $stmt = $table->select(['name' => $_POST['name']]);




            $_SESSION['test_info'] = (array)$stmt->current();


            header('Location: /tests/questions');
            return;
        }

        $view = new \Quiz\View\Tests\Form();
        $view->render([
            'title' => 'Create a new test',
            'data' => $_POST,
            'messages' => $validator->getMessages(),
        ]);
    }


    public function runUpdate()
    {



        if (!isset($_GET['id'])) {
            header('Location: /tests');
            return;
        }



        $adapter = \Quiz\Service\DB::getAdapter();
        
        $question_array = $this->getQuestions();

        $answer_array = $this->getAnswers();



        $validator = $this->getValidator(true);
            if ($_POST && $validator->check($_POST)) {

            $table = new TableGateway('tests', $adapter);
            $tableAns = new TableGateway('answers', $adapter);
            $tableQt = new TableGateway('questions', $adapter);
            $table->update(['name' => $_POST['name'],'id' => $_GET['id']]);

            
            foreach($_POST as $element => $def){
                if(substr($element,0,1) == 'a'){
                    $tableAns->update(['text_answer' => $def,'correct' => isset($_POST['c'.substr($element,1)]) ? 1 : 0],['id' => substr($element,1)]);

                  
                } elseif(substr($element,0,1) == 'q') {
                    $tableQt->update(['text_question' => $def],['id' => substr($element,1)]);

                    
                }
            }

            header('Location: /tests');
            return;
        }

        

        $view = new \Quiz\View\Tests\UpdateForm();
        $view->render([
            'title' => 'Task\'s account editing',
            'questions' => $question_array,
            'answers' => $answer_array,
            'test_name' => $this->getTestName(),
            'messages' => $validator->getMessages(),

        ]);
    }


    public function runAddQuestion(){
        $adapter = \Quiz\Service\DB::getAdapter();
        $table = new TableGateway('questions', $adapter);


        if(isset($_GET)){
        $table->insert(['id_test' => intval($_GET['id']),'text_question' => '']);
            
            
            
        header('Location: /tests/update?id='.$_SESSION['test_id']);
            
        $view = new \Quiz\View\Tests\UpdateForm();
        $view->render([
            'title' => 'Task\'s account editing',
            'questions' => $this->getQuestions($_SESSION['test_id']),
            'answers' => $this->getAnswers(),
            'test_name' => $this->getTestName($_SESSION['test_id']),

        ]);
        
        } else {
            header('Location:/tests');
            return;
        }
    }

    public function runDeleteQuestion(){
        $adapter = \Quiz\Service\DB::getAdapter();
        
        $table = new TableGateway('questions', $adapter);
        $tableAns = new TableGateway('answers', $adapter);
        $adapter = \Quiz\Service\DB::getAdapter();

        if(isset($_GET)){
            $table->delete(['id' => $_GET['id']]);

            
            $tableAns->delete(['id_question ' => $_GET['id']]);
            
            
            
            $view = new \Quiz\View\Tests\UpdateForm();
            header('Location: /tests/update?id='.$_SESSION['test_id']);
            $view->render([
                'title' => 'Task\'s account editing',
                'questions' => $this->getQuestions($_SESSION['test_id']),
                'answers' => $this->getAnswers(),
                'test_name' => $this->getTestName($_SESSION['test_id']),
    
            ]);
        } else {
            header('Location:/tests');
            return;
        }
    }


    public function runDeleteAnswer(){
        $adapter = \Quiz\Service\DB::getAdapter();
        
        $table = new TableGateway('answers', $adapter);

        if(isset($_GET)){
            $table->delete(['id' => $_GET['id']]);

            
            
            
        $view = new \Quiz\View\Tests\UpdateForm();
        header('Location: /tests/update?id='.$_SESSION['test_id']);
        $view->render([
            'title' => 'Task\'s account editing',
            'questions' => $this->getQuestions($_SESSION['test_id']),
            'answers' => $this->getAnswers(),
            'test_name' => $this->getTestName($_SESSION['test_id']),

        ]);
        } else {
            header('Location:/tests');
            return;
        }
    }

    public function runAddAnswer(){
        $adapter = \Quiz\Service\DB::getAdapter();
        
        $table = new TableGateway('answers', $adapter);


        if(isset($_GET)){
            $table->insert(['id_question' => intval($_GET['id']),'text_answer' => '','correct' => 0 ]);
            
            
        header('Location: /tests/update?id='.$_SESSION['test_id']);
            
        $view = new \Quiz\View\Tests\UpdateForm();
        $view->render([
            'title' => 'Task\'s account editing',
            'questions' => $this->getQuestions($_SESSION['test_id']),
            'answers' => $this->getAnswers(),
            'test_name' => $this->getTestName($_SESSION['test_id']),

        ]);
        
        } else {
            header('Location:/tests');
            return;
        }
    }

    public function runDelete()
    {

        $adapter = \Quiz\Service\DB::getAdapter();
        $table = new TableGateway('questions', $adapter);
        $tableAns = new TableGateway('answers', $adapter);
        $tableTest = new TableGateway('tests', $adapter);



        if (isset($_POST['id'])) {
            

            $stmt = $table->select(['id' => $_POST['id']]);
            
            $arrayOfIdQuestions = [];
            foreach($stmt->current as $qst){
                $arrayOfIdQuestions [] = $qst;
            }

            foreach ($arrayOfIdQuestions as $qstn) {

                foreach ($qstn as $id) {
                    $tableAns->delete(['id_question' => $id]);
                    
                }
            }

            foreach ($arrayOfIdQuestions as $qstn) {
                $table->delete(['id' => $qstn['id']]);

            }

            $tableTest->delete(['id' => $_POST['id']]);
            

            header('Location: /tests');
            return;
        }

        if (!isset($_GET['id'])) {
            header('Location: /tests');
            return;
        }

        $stmt = $tableTest->select(['id' => $_GET['id']]);
        
        $test = [];
        foreach($stmt->current() as $test){
            $test [] = $test;
        }
        if (!$test) {
            header('Location: /tests');
            return;
        }



        $view = new \Quiz\View\Tests\DeleteForm();


        $view->render([
            'title' => 'Delete test',
            'task' => $test,
            'url' =>   [
                'approve' => '/tests/delete',
                'cancel' => '/tests',
            ]
        ]);
    }



    

    private function getQuestions($id = '')
    {
        $adapter = \Quiz\Service\DB::getAdapter();

        $table = new TableGateway('questions', $adapter);
        $id = ($id == '' ? intval( $_GET['id']) : $id);


        $stmt = $table->select(['id_test' => $id]);

        $questions  = [];
        foreach($stmt as $row){
            $questions [] = (array)$row;
        }





        return $questions;
    }

    private function getAnswers()
    {
        $adapter = \Quiz\Service\DB::getAdapter();

        $answer_array = [];
        $table = new TableGateway('answers', $adapter);
        $stmt = $table->select();

        foreach($stmt as $row){
            $answer_array [] = (array)$row;
        }
        return $answer_array;
    }

    private function getTestName($id = '')
    {
        
        $adapter = \Quiz\Service\DB::getAdapter();
        $table = new TableGateway('tests', $adapter);
        $stmt = $table->select(['id' => $id == '' ? intval( $_GET['id']) : $id]);
        
        $test  = [];
        foreach($stmt as $row){
            $test [] = (array)$row;
        }


        return $test;
    }

    private function getValidator($isUpdate = false)
    {
        $validator = new \Quiz\Service\Validator();
        $adapter = \Quiz\Service\DB::getAdapter();
        foreach ($_POST as $id => $def) {



            $validator->setRule($id, function ($value) {

                return !is_null($value) && mb_strlen($value) > 0;
            }, 'You leaved this field empty, returned to the intial set up');

            
            

        }
        $validator->setRule('name', function ($value) {

            return !is_null($value) && mb_strlen($value) > 0;
        }, 'You leaved this field empty, returned to the intial set up');


        return $validator;
    }
}
