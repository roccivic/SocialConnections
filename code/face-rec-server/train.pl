#!/usr/bin/perl

my $input = `curl -s http://socialconnections.placella.com/ninja.php 2>/dev/null`;
chomp($input);
my @lines = split(/\n/, $input);
$path = shift(@ARGV) || '.';
foreach my $line (@lines) {
	chomp($lines[$line]);
	if ($lines[$line].length) {
		my @group = split(/,/, $lines[$line]);
		my $gid = shift(@group);
		my $command = "cd " . $path . " && ./face-train " . $gid . " '" . join(',', @group) . "'\n";
		print $command;
		`$command`;
	}
}