import java.io.*

public class linked{

	private Node head;
	private int listCount;

	// initial Class Constuctor 

	public linked(){

	}

	public linkedList(){
		head = new Node(null);
		listCount = 0;
	}

	/* Method for add() */

	public void add(Object data){


		Node preFront = new Node(data);
		
		// Current
		Node current = head;

		while(current.getNext() != null){
			current = current.getNext();
		}
		current.setNext(preFront); // Will Complete this line
		listCount++;
	}

	/* Method to implement remove()*/

	public Boolean remove(int index){
		// Check if index is out of bounds

		if(index < 1){
			return false;
		}

		Node current = head;
		if(head != null){
			for (int i = 0; i < index; i++){
				if(current.getNext() == null){
					return false;
				}

				// Setting to newer Value;
				current = current.getNext();
			}

			// decrement the length by one
			listCount = getLength()-1;
			return true;
		}
		return false;
	}

	/* Method for getLength() */

	public int getLength(){
		Node current = head;
		if(head == null){
			return 0;
		}
		int count = 0;
		while(current.getNext() != null){
			current = current.getNext();
			count++;
		}
		return count;
	}

	/* Method for search() returning the index value*/

	public int search(int x){

	}

	/* Method value at certain index getIndex() */

	public int getIndex(Object i){
		if (i <= 0){
			return null;
		}
		int counter = 0;
		Node current = null;
		if(head != null){
			current = head.getNext();
			for(int j= 0; j< i ; j++){
				// For Safety
				if(current.getNext() == null){
					return null;
				}
				current = current.getNext();
			}
			return current.getData();
		}else{

		}
		return current;
	}

	// Creating Node class inorder complete above task's

	private class Node {
		// Refrence to the next code
		Node next;
		// Data will be carried by this node
		Object data;
		// init constructor
		public Node(Object dataValue){
			next = null;
			data = dataValue;
		} 
		
		// Recieve the Data
		
		public Object getData(){
			return data;
		}

		// setData

		public void setData(Object dataValue){
			data = dataValue;
		}

		// Get Next
		public Node getNext(){
			return next;
		}

		public void setNext(Node nextValue){
			next = nextValue;
		}
	}
}