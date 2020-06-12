import webbrowser

inputs = open(input("nome del file: ")+".csv")
output = open("./ciao.csv", "w")
datain = []

years = inputs.readline().rstrip("\n").split(";")
years.pop(0)
country = []
cycle = 0
for i in inputs.readlines():
    cycle +=1
    i = i.rstrip("\n")
    data = i.split(";")
    country.append(data[0])
    data.pop(0)
    datain.append(data)

for row in datain:
    for cell in datain[0]:
        if cell == "n/a":
            cell = ""

for year in years:
    for row in range(len(datain)):
        output.write(country[row] + "," + year + ","
        + str(datain[row][years.index(year)]).replace(",",".") + "\n")

webbrowser.open('http://localhost/Import.php?campo='
    +input("nome del campo: ")+'&tabella='+input("nome tabella: "))