all:
	dot -T png test.dot -o test.png
	dot -T png resource.dot -o resource.png
	dot -T png package.dot -o package.png

	open test.png resource.png package.png
