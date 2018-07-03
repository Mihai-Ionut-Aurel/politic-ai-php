import io

def load_vectors(fname):
    fin = io.open(fname, 'r', encoding='utf-8', newline='\n', errors='ignore')
    n, d = map(int, fin.readline().split())
    data = {}
    i=0
    for line in fin:
        if i % 4 == 0:
            tokens = line.rstrip().split(' ')
            data[tokens[0]] = map(float, tokens[1:])
        i+=1
    return data
