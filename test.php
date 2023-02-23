<?php

// donnne le numero de la semaine actuelle
echo date('W') . "\n";


// verifie que le nombre de la semaine est paire ou impaire
if(date('W')%2 == 0){
    echo 'semaine paire' ;
}else{
   echo ' semaine impaire';
}
