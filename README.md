ExpressionEngine Bootstrap
==============

Bootstrap your EE environment anywhere you want. Can be useful when you need to run a cronjob or - like me - need to combine both ZendFramework and ExpressionEngine in one project. 

**Also includes a handy EE template parser.**

Example of using the EE super global in a part ZendFramework 1.x, part ExpressionEngine 2.x environment.

    <?php
    $system_path = APPLICATION_PATH . '/../ee2';
    include(APPLICATION_PATH . '/bootstrap-ee2.php');

    $EE = get_instance();

    if (!isset($EE->session->userdata) || $EE->session->userdata['can_access_cp'] != 'y') {
        $this->_response->setRedirect('/');
    }

    if (isset($EE->session->userdata['screen_name'])) {
        $this->view->loggedInUsername = $EE->session->userdata['screen_name'];
    }

Example of using the template parser. Documentation provided in file.

    <?php
     $system_path = APPLICATION_PATH . '/../ee2';
     include(APPLICATION_PATH . '/bootstrap-ee2.php');
     echo parse_template('includes', 'header');

