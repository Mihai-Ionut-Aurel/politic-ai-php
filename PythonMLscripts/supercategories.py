import csv
from sklearn.feature_extraction.text import CountVectorizer
from sklearn import svm
import string
import re
import sys
import numpy as np
#Compare every two subjects for similarities in keywords.

csv.field_size_limit(1000000)

#Either do a machine learning thing with fixed 'supercategories' or find similarity and brand as supercategory later.
trainedids = []
trainedsamples = []
trainedtitles = []
trainedtexts= []
categories = []
categoriesperindex={}

ids = []
samples = []
texts = []
titles = []


punctuation = '\\' + '|\\'.join(string.punctuation)
with open('categoriestotal.csv',newline='\n', encoding="utf-8", errors="surrogateescape") as csvfile:
	reader = csv.reader(csvfile, delimiter=',', quotechar='"')
	for row in reader: 
		if row[4] == str(1):
			trainedids.append(row[0])
			trainedsamples.append(row)
			trainedtexts.append( re.sub(punctuation, '', row[2]))
			trainedtitles.append(row[1])
			categories.append(row[3])
			if not row[0] in categoriesperindex:
				categoriesperindex[row[0]] = []
			categoriesperindex[row[0]].append(row[3])
		else:
			ids.append(row[0])
			samples.append(row)
			texts.append( re.sub(punctuation, '', row[2]))
			titles.append(row[1])
			
			
vectorizertext = CountVectorizer()
bowtext = vectorizertext.fit_transform( trainedtexts ).todense()
vectorizertitle = CountVectorizer()
bowtitle = vectorizertitle.fit_transform( trainedtitles ).todense() 

#Create array of SVMs for each category and train


svmarray = {}
for x in list(set(categories)):
	svmarray[x] = svm.LinearSVC()
	bowtotal = np.hstack((bowtext[0], bowtitle[0]))
	for i in range(1,len(trainedids)):
		bowtotal = np.vstack(( bowtotal , np.hstack((bowtext[i], bowtitle[i]))))
	catone = []
	for category in categories:
		if category is x:
			catone.append(1)
		else:
			catone.append(0)
	#print(catone)
	svmarray[x].fit(bowtotal,catone)

	
#Test on trainingset (awful I know)
results = {}	
for i in range(0, len(trainedids)):
	bowresult = np.hstack((bowtext[i], bowtitle[i]))
	results[trainedids[i]] = []
	for category, svm in svmarray.items():
		if svm.predict(bowresult) == 1:
			results[trainedids[i]].append(category)

correct = 0
for id, result in results.items():
	for category in result:
		if id in categoriesperindex:
			if category in categoriesperindex[id]:
				correct += 1
print(correct/len(trainedids))
	
#Test each SVM on each subject title+text (below)
#Export and upload results, improve them and repeat


#with open('totalsubjects.csv',newline='\n', encoding="utf-8", errors="surrogateescape") as csvfile:
#	reader = csv.reader(csvfile, delimiter=',', quotechar='"')
#	for row in reader:
#		if row[0] not in trainedids:
#			ids.append(row[0])
#			samples.append(row)
#			texts.append( re.sub(punctuation, '', row[2]))
#			titles.append(row[1])

results = {}	
bagtext = vectorizertext.transform(texts).todense()
bagtitle= vectorizertitle.transform(titles).todense()	

for i in range(0,len(texts)):
	bowresult = np.hstack((bagtext[i], bagtitle[i]))
	results[ids[i]] = []
	for category, svm in svmarray.items():
		if svm.predict(bowresult) == 1:
			results[ids[i]].append(category)
	
with open('resultcategories.csv','w',encoding="utf-8", errors="surrogateescape") as resultfile:
	for id, result in results.items():
		for category in result:
			resultfile.write('"' + id + '","' + category + '"\n')
		
		
		
		