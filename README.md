Methodology - unstable
======================
Methodology is an experimental library which incorporates some of the dynamic programming techniques in PHP 5.4.
Uses [SEL](http://symfony.com/doc/current/components/expression_language/index.html).

Use cases
=========

    use Methodology\Sugar\Scope;
    use Methodology\Context\Curry;

    $scope = new Scope();

1. Template methods
-------------------

    $scope->elements = '[2, 3, 5]'; 

### 1.1 Easy case

    $scope->template = function() {
        return $this->someStrategy($this->elements[2]);        
    };

    $scope->someStrategy = function($element) {
        return $element * 2;    
    };

    $result = $scope->template();

### 1.2 Tricky one with placeholders

    $scope->printElements = function() {
        foreach($this->elements as $element) {
            
            $printer = $this->_placeholder('printer', function($element) {
                print "$element, ";			
            });
            $printer($element);
        }	
    };

    $scope->printElements(); // 2, 3, 5,

    $scope->printer = function($element) {
        print "'$element', ";
    };

    $scope->printElements(); // '2', '3', '5',

2. Dynamic scopes
-----------------

    $scope->pay = function($who) {
        print "$who pays for collage\n";    
    };
    
    $daddy = $scope->newChild();
    $johny = $daddy->newChild();

    $daddy->payForCollage = function() {
        $this->pay('Daddy');    
    };
    
    $johny->foundJob = function() {
        $this->payForCollage = function() { 
            $this->pay('Johny');
        };
    };

    $johny->payForCollage(); // 'Daddy pays for collage.'
    $johny->foundJob();
    $johny->payForCollage(); // 'Johny pays for collage.'
    $daddy->payForCollage(); // 'Daddy pays for collage.'


3. Method decorators
--------------------

    setlocale(LC_MONETARY, 'en_US');

    $scope->price = 345.00;
    $scope->discount = 20.00;

    $scope->withCurrency = function($price) {
        return money_format("%i", $price); 
    };

    $scope->getPrice = function() {
        return $this->price - $this->discount;
    };
    $scope->getPrice->propagates('withCurrency($1)');

    print $scope->getPrice();  // USD 325.00 


4. Method-scope variables
--------------------------

    $scope->phone = '"(840) 595-2135"';

    $scope->callDaddy = function() {
        print "Dialing {$this->phone} ..\n";    
    };

    $scope->callDaddy(); // Dialing (840) 595-2135 ..

    $scope->callMummy = $scope->callDaddy->overclone('phone', '"(964) 474-2139"');

    $scope->callMummy(); // Dialing (964) 474-2139 ..
    $scope->callDaddy(); // Dialing (840) 595-2135 ..

    $scope->callDaddy->override('phone', '"(984) 505-3745"'); // Daddy has changed his phone.
    $scope->callDaddy(); // Dialing (984) 505-3745 ..

    print $scope->phone . "\n"; // (840) 595-2135

5. AO programming
-----------------

    $scope->isAdmin = function($user) {
        if($user->type !== "admin") {
            print "Do not have rights\n";	
            $this->_stopDependencyChain();	
        }
        return $user;
    };

    $scope->askForPermission = function($user) {
        print "Are you sure you want to continue? All data will be lost. (Y/n) ";   

        if(trim(fgets(STDIN)) !== 'Y') {
            print "ABORTED\n";
            $this->_stopDependencyChain();	
        }

        return $user;
    };

    $scope->removeAllData = function($user = 'isAdmin($1)') {
        print "User {$user->name} removed all data from the system\n";
    };

    $scope->removeAllData->depends($scope->askForPermission);

    $user = new stdClass();
    $user->name = 'John Foo';
    $user->type = 'mod';

    $scope->removeAllData($user); // 'Do not have rights'

    $user->type = 'admin';
    $scope->removeAllData($user);  // asks for permission and proceeds



6. Event bubbling / Chain of responsibility
-------------------------------------------
Check `_stopDependencyChain()` and `_stopPropagationChain()` methods of `Methodology\ContextProxy`.

7. Currying
-----------

    $scope->printNums = new Curry(function($a, $b, $c) {
        print "$a, $b, $c\n";
    });

    $scope->nums = '1 .. 24';

    $scope->printTuples = function() {
        foreach($this->nums as $tuple) {
            $this->printNums($tuple);
        }	
    };

    $scope->printTuples();  // 1, 2, 3
                            // 4, 5, 6
                            // 7, 8, 9 
                            // ... 

    $scope->printTuples->override('printNums', new Curry(function($a, $b) {
        print "$a, $b\n"; 
    }));

    $scope->printTuples();  // 1, 2
                            // 3, 4 
                            // 5, 6
                            // ... 
    
8. Collectors
-------------

    $scope->fname = '"boofile"';
    $scope->cnt = 5;

    $scope->fromTxt = function() {
        $hdl = fopen($this->fname . ".txt", 'r');
        try {
            while($line = fgets($hdl)) {
                $this->_collect($line);
            }
        } catch(Exception $e) {
            fclose($hdl);
            throw $e;
        }
    };

    $scope->fromXml = function() {
        $xml = simplexml_load_file($this->fname . ".xml");
        
        foreach($xml->number as $num) {
            $this->_collect((int)$num->__toString());
        }
    };

    $scope->fromCli = function() {
        while(true) $this->_collect(fgets(STDIN));
    };

    $scope->calcAverage = function($data) {
        $values = $data->collect($this->cnt);
        return array_sum($values) / $this->cnt;
    };

    print $scope->calcAverage($scope->fromTxt); // average from first 5 lines
    print $scope->calcAverage($scope->fromXml); // average value from 5 `number` child nodes of root node
    print $scope->calcAverage($scope->fromCli); // asks 5 times for value and calcs average of them 


9. SEL
------ 

All string values assigned to `Methodology\Scope` are treated as SEL expressions.

    $scope->inRange = '234 in 200 .. 300'; // true

    $scope->power = function($a, $b) {
        return pow($a, $b);
    };

    $scope->val = 3;

    $scope->result = 'power(val, 2) + 1'; // 10

    $scope->strVal = '"string values have to be wrapped in extra double quotes"';
     