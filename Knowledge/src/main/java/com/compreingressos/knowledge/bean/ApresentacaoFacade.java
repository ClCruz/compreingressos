/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.Apresentacao;
import com.compreingressos.knowledge.model.Evento;
import java.util.Date;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class ApresentacaoFacade extends AbstractFacade<Apresentacao> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public ApresentacaoFacade() {
        super(Apresentacao.class);
    }

    @Override
    public List<Apresentacao> findAll() {
        return getEntityManager().createNamedQuery("Apresentacao.findAll").getResultList();
    }

    public Date findMinDate(Evento evento) {
        return (Date) getEntityManager().createNamedQuery("Apresentacao.findMinDate").setParameter("evento", evento).getSingleResult();
    }

    public Date findMaxDate(Evento evento) {
        return (Date) getEntityManager().createNamedQuery("Apresentacao.findMaxDate").setParameter("evento", evento).getSingleResult();
    }
    
    public void remove(Evento evento){
        getEntityManager().createNativeQuery("DELETE FATO_KBASE_APRESENTACAO WHERE ID_KBEV = "+ evento.getId()).executeUpdate();
    }
    
}
