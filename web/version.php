<?php

// Tue Jun 24 17:58:28 UTC 2014
echo "rpm version: ";
$rpm = system('rpm -qa | grep octopush');
ereg("([[:alnum:]]+)-([[:alnum:]]+)-([[:alnum:]]+)_([[:alnum:]]+)", $rpm, $regs);

echo "<br />\n";
echo "<br />\n";
echo "more links: ";
echo "<br />\n";
echo "<ul>";
echo "<li>";
echo '<a href="http://jenkins-rls.olx.com.ar/job/Build_Octopush_Artifact/'.$regs[2].'/console">jenkin\'s build</a> ';
echo "</li><li>";
echo '<a href="https://github.com/olx-inc/octopush/tree/'.$regs[4].'">code in github</a>';
echo "</li>";
echo "</ul>";
echo "<br />\n";
