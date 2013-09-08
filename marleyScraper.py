from urllib2 import urlopen
import xml.etree.ElementTree as ET
import json

'''
This is for generating the json necessary for the d3.js marley train diagram
morining Fremont-Milbrae line
@author ttam
@since 2013
'''

def getStationList(key):
    stationList = {}
    tree = ET.parse(urlopen('http://api.bart.gov/api/stn.aspx?cmd=stns&key=MW9S-E7SL-26DU-VV8V'))
    root = tree.getroot()
    for stn in root[1]:
        code = stn[1].text
        station = {}
        station['name'] = stn[0].text
        station['latitude'] = stn[2].text
        station['longitude'] = stn[3].text
        stationList[code] = station
    return stationList

def getRouteList(routeNum, date, key, stationList):
    output = {}
    routeList = []
    tree = ET.parse(urlopen('http://api.bart.gov/api/sched.aspx?cmd=routesched&route=' +
        str(routeNum) + '&date=' + date + '&key=' + key))
    root = tree.getroot()
    for tinfo in root[3]:
        train = {}
        stationLine = []
        distance = 100
        for i in range(1, len(tinfo)):
            stopA = tinfo[i-1]
            stopB = tinfo[i]
            
            lineEntry = {}
            lineEntry['time1'] = stopA.attrib['origTime'].replace(" ","")
            lineEntry['time2'] = stopB.attrib['origTime'].replace(" ","")
            lineEntry['y1'] = distance
            lineEntry['y2'] = distance + 100 
            routeList.append(lineEntry)
            
            station = stationList[stopA.attrib['station']]
            #FIXME hack for now
            station['distance'] = distance
            stationLine.append(station)
            distance += 100
    output['stations'] = stationLine
    output['routes'] = routeList
    return output

if __name__ == "__main__":

    routeNum = 5                    # Milbrae/Fremont northbound)
    date='9/9/2013'                 # (pick a weekday date)
    key= 'MW9S-E7SL-26DU-VV8V'      # default api key
    
    stationList = getStationList(key)
    jsonstr = {}
    jsonstr = getRouteList(routeNum, date, key, stationList)

    jsonOut = open('bartSchedule.json','w')
    jsonOut.write(json.dumps(jsonstr, indent=4, separators=(',',':')))
    jsonOut.close()


