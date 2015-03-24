cols = open("cols.csv", "r")
output = open("cols.xml","w")

header = cols.readline()
header = header.strip('\n').split('\t')
for i,h in enumerate(header):
	header[i] = h.strip()

output.write("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
output.write("<table>\n")
print 
for line in cols:
	output.write("\t<column>\n")
	
	data = line.strip('\n').split('\t')
	
	for i,d in enumerate(data):
		data[i] = d.strip()

	output.write("\t\t<field>"+str(data[0])+"</field>\n")
	output.write("\t\t<type>"+str(data[1]).upper()+"</type>\n")

	if data[2] == "NO":
		output.write("\t\t<null>NOT NULL</null>\n")
	else:
		output.write("\t\t<null></null>\n")

	if data[3] == "PRI":
		output.write("\t\t<key>PRIMARY KEY</key>\n")
	else:
		output.write("\t\t<key></key>\n")
	if data[2] == "NO":
		output.write("\t\t<default></default>\n")
	else:
		output.write("\t\t<default>DEFAULT "+str(data[4])+"</default>\n")
		
	if len(data) < 6 or data[5] == "":
		output.write("\t\t<extra></extra>\n")
	else:
		output.write("\t\t<extra>"+str(data[5])+"</extra>\n")
	if "decimal" in str(data[1]):
		output.write("\t\t<precision>2</precision>\n")
	else:
		output.write("\t\t<precision></precision>\n")
	if "decimal" in str(data[1]):
		output.write("\t\t<percentage>false</percentage>\n")
	else:
		output.write("\t\t<percentage></percentage>\n")
	output.write("\t\t<comment></comment>\n")


	output.write("\t</column>\n")

output.write("</table>")