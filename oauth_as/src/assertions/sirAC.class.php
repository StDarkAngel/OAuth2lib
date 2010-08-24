<?php

require_once("assertionChecking.interface.php");
require_once 'policy.class.php';

/**
 * Class that checks if a given sir assertion is valid.
 */
class sirAssertionChecking implements IAssertionChecking {

    private $assertion;
    private $policy;
    private $personId;
    private $error;
    private $scope;
    private $sho;

    /**
     * Load the policies from a  given file.
     * @param <type> $rulesf The archive file with the policy.
     */
    public function __construct($scope, $rulesf = "config/policies.xml") {
        $this->error = false;
        $this->scope = $scope;
        $this->policy = array();
        if (0 == sizeof($this->getPolicy($rulesf))) {
            error_log("ERROR EXTRAYENDO POLÍTICAS");
            $this->error = true;
        }
    }

    /**
     * Function that checks if the assertion is authorized
     * @param array $assertion Assertion to check
     * @return bool. True if the policy matches the assertion.
     */
    public function checkAssertion($assertion) {
        $this->assertion = $assertion;
        $dev = false;
        if ($this->matchRules() != false) {
            if (isset($this->assertion['uid'])) {
                $this->personId = $this->assertion['uid'];
           		$this->sho = $this->assertion['sHO'];
                $dev = true;
            }
        }
        return $dev;
    }

    /**
     * Function that decides if the assertion matches the policy,
     * @param <type> $assertion
     * @return bool. True if the policy matches the assertion.
     */
    protected function matchRules() {
        $dev = true;
        if (sizeof($this->policy) > 0) {
            foreach ($this->policy as $pol) {
                $dev = $dev && $pol->checkPolicy($this->assertion);
            }
        } else {
            $dev = false;
        }
        return $dev;
    }

    /**
     * Function that loads the Policy from a given file
     * @param <type> $rulesfile File of the policy
     * @return AssertionPolicy. The Policy.
     */
    protected function getPolicy($rulesfile) {
        $xml = simplexml_load_file($rulesfile);
        if (strcmp($xml->getName(), "AssertionList") == 0)
            foreach ($xml->children() as $policy) {
                if (0 == strcmp("papi", $policy['type']))
                    foreach ($policy->children() as $pol) {
                        if (0 == strcmp($this->scope, $pol['scope']))
                            foreach ($pol->Policy as $p) {
                                $this->policy[] = new AssertionPolicy($p);
                            }
                    }
            }
        return $this->policy;
    }

    /**
     * Person Id getter
     * @return String: The scope.
     */
    public function getPersonId() {
        return $this->personId;
    }

    public function getError() {
        return $this->error;
    }
    public function getSHO(){
        return $this->sho;
    }

}
?>