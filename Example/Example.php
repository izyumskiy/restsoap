<?php

class Example 
{

    private function getResponse($data, $status = 200, $errorMessage = '') {
        $resp = ['status' => $status,
                 'error'  => $errorMessage,
                 'data'   => $data];
        return $resp;
    }
    
    public function postBooks($params) {
        return $this->getResponse(true);
    }
    
    public function updateBooks($params) {
        return $this->getResponse(true);
    }
    
    public function getBooks($params) {     
        $books = [351 => ['book_id' => 351, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'price' => 15], 
                  387 => ['book_id' => 387, 'title' => 'The Brothers Karamazov', 'author' => 'Fyodor Mikhailovich Dostoyevsky', 'price' => 27],
                  236 => ['book_id' => 236, 'title' => 'One Hundred Years of Solitude', 'author' => 'Gabriel García Márquez', 'price' => 25]];
                
        $id = isset($params['book_id']) && (int)$params['book_id'] > 0 ? $params['book_id'] : null;  
        if(is_null($id)) {
            return $this->getResponse($books);
        } else {
            return $this->getResponse([$id => $books[$id]]);
        }
    }
    
    public function postFile($params) {
        return 'FILE:' . $params['http_body'];
    }
    
    public function getFile($params) {
        return 'FILE-CONTENT:' . $params['id'];
    }
}