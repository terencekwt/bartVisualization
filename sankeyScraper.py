from urllib2 import urlopen
import xml.etree.ElementTree as ET
import json

'''
This is for generating the json necessary for the d3.js sankey diagram 
for Bart trains.
@author ttam
@since 2013
'''
jsonstr = {}
jsonstr["nodes"] = []
jsonstr["links"] = []
stationKeyMap = {}

tree = ET.parse(urlopen('http://api.bart.gov/api/stn.aspx?cmd=stns&key=MW9S-E7SL-26DU-VV8V'))
root = tree.getroot()
for station in root[1]:
    stationKeyMap[station[1].text] = station[0].text
#need to put it in the same ouder as the mapping of key
for station in stationKeyMap.values():
    jsonstr["nodes"].append({"name": station})

for i in range(len(stationKeyMap.keys())):
    print i, stationKeyMap.keys()[i]

#need to construct a DAG, therefore could only choose all northbound routes or all southbound
routeNums = [4, 6]
#routeNums = [2, 3, 6, 8]
for i in routeNums:
    print i
    tree = ET.parse(urlopen('http://api.bart.gov/api/route.aspx?cmd=routeinfo&route=' +
        str(i) + '&key=MW9S-E7SL-26DU-VV8V'))
    root = tree.getroot()
    #root => routes => route => config
    routeList = root[2][0][10]
    for i in range(1,len(routeList)):
        print stationKeyMap[routeList[i-1].text] ,'-', stationKeyMap[routeList[i].text]
        jsonstr["links"].append({"source":stationKeyMap.keys().index(routeList[i-1].text),
            "target":stationKeyMap.keys().index(routeList[i].text),
            "value" : 1})

jsonOut = open('bart.json','w')
jsonOut.write(json.dumps(jsonstr, indent=4, separators=(',',':')))
jsonOut.close()


