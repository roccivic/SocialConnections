#!/usr/bin/perl

my $input = `curl -s http://109.255.78.105/socialconnections/ninja.php 2>/dev/null`;
chomp($input);
my @lines = split(/\n/, $input);
foreach my $line (@lines) {
	chomp($lines[$line]);
	if ($lines[$line].length) {
		my @group = split(/,/, $lines[$line]);
		my $gid = shift(@group);
		my $command = "./face-train " . $gid . " '" . join(',', @group) . "'\n";
		`$command`;
	}
}