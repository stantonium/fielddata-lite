//
//  ContentView.swift
//  TestConnection
//
//  Created by Hopp, Dan on 1/31/23.
//

import SwiftUI

struct ContentView: View {
    
    @State var models: [ResponseModel] = []
    // what is going on? test again
    var body: some View {
        // Create VStack
        VStack {
            // now show in list
            // to show in list, model class must be identifiable
            
            List (self.models) { (model) in
                HStack {
                    // they are optional
                    Text(model.type ?? "").bold()
                }
            }
            
        }.onAppear(perform: {
            // send request to server
            
            guard let url: URL = URL(string: "http://covid-samples01.ornl.gov/fielddata-lite/php/routesTestQuery.php") else {
                print("invalid URL")
                return
            }
            
            var urlRequest: URLRequest = URLRequest(url: url)
            urlRequest.httpMethod = "GET"
            URLSession.shared.dataTask(with: urlRequest, completionHandler: { (data, response, error) in
                // check if response is okay
                
                guard let data = data else {
                    print("invalid response")
                    return
                }
                
                // convert JSON response into class model as an array
                do {
                    self.models = try JSONDecoder().decode([ResponseModel].self, from: data)
                } catch {
                    print(error.localizedDescription)
                }
                
            }).resume()
        })
    }
}
struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView()
    }
}
