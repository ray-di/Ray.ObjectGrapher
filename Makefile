all:
	dot -T png dependency.dot -o dependency.png
	open dependency.png
